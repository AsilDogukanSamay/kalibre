<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\ContactMessage;
use App\Support\LoginThrottle;
use Throwable;

/**
 * Yonetim paneli.
 *
 * Semadaki status alani (new / read / archived) bu panel icin hazirdi ama
 * arayuzu yoktu; gelen talep kimseye gorunmuyordu. Panel o boslugu kapatir.
 *
 * Guvenlik: parola .env'de yalnizca bcrypt ozeti olarak durur, oturum
 * girisin ardindan yenilenir, durum degistiren her istek CSRF belirteci
 * tasir ve basarisiz girisler IP basina sinirlidir.
 */
final class AdminController extends Controller
{
    /** Sayfa basina kayit. */
    private const PER_PAGE = 20;

    /** Sema degerlerinin Turkce karsiliklari, tek kaynak. */
    private const LABELS = [
        'new'      => 'Yeni',
        'read'     => 'Okundu',
        'archived' => 'Arşiv',
    ];

    // ----------------------------------------------------------------
    // Giris / cikis
    // ----------------------------------------------------------------

    public function loginForm(Request $request): void
    {
        if ($this->user() !== null) {
            Response::redirect('/yonetim');
            return;
        }

        $throttle = new LoginThrottle();

        $this->view('admin/login', [
            'csrf'      => Session::csrfToken(),
            'error'     => Session::flash(),
            'remaining' => $throttle->remaining($request->ip()),
            'pageTitle' => 'Giriş',
        ], 200, 'layouts/admin');
    }

    public function login(Request $request): void
    {
        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.');
            Response::redirect('/yonetim/giris');
            return;
        }

        $throttle = new LoginThrottle();
        $ip       = $request->ip();

        if ($throttle->isLocked($ip)) {
            Session::flash(sprintf(
                'Çok fazla hatalı deneme. %d dakika sonra tekrar deneyin.',
                (int) ceil($throttle->retryAfter($ip) / 60)
            ));
            Response::redirect('/yonetim/giris');
            return;
        }

        if (!$this->credentialsMatch($request->input('user'), $request->input('password'))) {
            $throttle->recordFailure($ip);
            // Hangi alanin yanlis oldugu soylenmez: kullanici adi sayimini engeller.
            Session::flash('Kullanıcı adı veya parola hatalı.');
            Response::redirect('/yonetim/giris');
            return;
        }

        $throttle->clear($ip);
        Session::regenerate();
        Session::set('admin_user', $request->input('user'));

        Response::redirect('/yonetim');
    }

    public function logout(Request $request): void
    {
        if (Session::verifyCsrf($request->input('_csrf'))) {
            Session::destroy();
        }

        Response::redirect('/yonetim/giris');
    }

    // ----------------------------------------------------------------
    // Liste
    // ----------------------------------------------------------------

    public function index(Request $request): void
    {
        $user = $this->user();
        if ($user === null) {
            Response::redirect('/yonetim/giris');
            return;
        }

        $filter = $request->input('durum');
        $filter = in_array($filter, ContactMessage::STATUSES, true) ? $filter : null;
        $page   = max(1, (int) $request->input('sayfa', '1'));

        try {
            $repository = new ContactMessage();
            $total      = $repository->countByStatus($filter);
            $pageCount  = max(1, (int) ceil($total / self::PER_PAGE));
            $page       = min($page, $pageCount);

            $messages = $repository->page($filter, self::PER_PAGE, ($page - 1) * self::PER_PAGE);
            $counts   = $repository->statusCounts();
        } catch (Throwable $e) {
            error_log('[admin] ' . $e->getMessage());
            $this->view('admin/hata', ['adminUser' => $user, 'pageTitle' => 'Bağlantı hatası'], 503, 'layouts/admin');
            return;
        }

        $this->view('admin/index', [
            'messages'  => $messages,
            'counts'    => $counts,
            'labels'    => self::LABELS,
            'filter'    => $filter,
            'page'      => $page,
            'pageCount' => $pageCount,
            'total'     => $total,
            'csrf'      => Session::csrfToken(),
            'flash'     => Session::flash(),
            'backTo'    => $this->backTo($filter, $page),
            'adminUser' => $user,
            'pageTitle' => 'Talepler',
        ], 200, 'layouts/admin');
    }

    public function updateStatus(Request $request): void
    {
        if ($this->user() === null) {
            Response::redirect('/yonetim/giris');
            return;
        }

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('Oturum doğrulaması başarısız.');
            Response::redirect('/yonetim');
            return;
        }

        $id     = (int) $request->input('id');
        $status = $request->input('durum');

        try {
            $repository = new ContactMessage();
            if ($id > 0 && $repository->updateStatus($id, $status)) {
                Session::flash(sprintf(
                    'KLB-%06d kaydı "%s" olarak işaretlendi.',
                    $id,
                    self::LABELS[$status] ?? $status
                ));
            }
        } catch (Throwable $e) {
            error_log('[admin] ' . $e->getMessage());
            Session::flash('Kayıt güncellenemedi.');
        }

        Response::redirect($this->safeRedirect($request->input('geri')));
    }

    // ----------------------------------------------------------------
    // Yardimcilar
    // ----------------------------------------------------------------

    private function user(): ?string
    {
        $user = Session::get('admin_user');
        return is_string($user) && $user !== '' ? $user : null;
    }

    /**
     * Kullanici adi sabit zamanli, parola bcrypt ozetiyle karsilastirilir.
     * Ozet .env'de tutulur; duz parola hicbir dosyada yazili degildir.
     */
    private function credentialsMatch(string $user, string $password): bool
    {
        $expectedUser = (string) Env::get('ADMIN_USER', '');
        $expectedHash = (string) Env::get('ADMIN_PASSWORD_HASH', '');

        if ($expectedUser === '' || $expectedHash === '') {
            return false; // panel yapilandirilmamis
        }

        // Kullanici adi yanlis olsa bile password_verify calistirilir:
        // yanit suresi iki durumda da ayni kalir (zamanlama sizintisi).
        $userOk = hash_equals($expectedUser, $user);
        $passOk = password_verify($password, $expectedHash);

        return $userOk && $passOk;
    }

    private function backTo(?string $filter, int $page): string
    {
        $query = http_build_query(array_filter([
            'durum' => $filter,
            'sayfa' => $page > 1 ? $page : null,
        ]));

        return '/yonetim' . ($query !== '' ? '?' . $query : '');
    }

    /**
     * Acik yonlendirme (open redirect) korumasi: yalnizca panel icindeki
     * gorece yollara donulur, disaridan gelen mutlak adres kabul edilmez.
     */
    private function safeRedirect(string $target): string
    {
        return preg_match('#^/yonetim(\?[^\s]*)?$#', $target) === 1 ? $target : '/yonetim';
    }
}
