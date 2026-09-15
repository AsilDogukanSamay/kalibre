<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\ContactMessage;
use RuntimeException;
use Throwable;

/**
 * POST /api/contact
 * Formu fetch() ile alir, dogrular, veritabanina yazar ve JSON doner.
 * Sayfa hicbir zaman yeniden yuklenmez.
 */
final class ContactController extends Controller
{
    private const RULES = [
        'full_name' => 'required|min:3|max:120',
        'email'     => 'required|email|max:180',
        'phone'     => 'required|phone|max:32',
        'message'   => 'required|min:10|max:2000',
    ];

    public function store(Request $request): void
    {
        // 1) Bal kupu: gorunmez alan doluysa bot kabul edilir.
        //    Basarili gibi yanit verilir ki bot geri bildirim almasin.
        if ($request->input('website') !== '') {
            Response::ok('Mesajınız alındı.');
            return;
        }

        // 2) Dogrulama
        $validator = new Validator($request->all(), self::RULES);

        if (!$validator->passes()) {
            Response::fail('Lütfen işaretli alanları düzeltin.', $validator->errors(), 422);
            return;
        }

        $data = $validator->validated();

        try {
            $repository = new ContactMessage();

            // 3) Spam freni
            $limit = Env::int('CONTACT_RATE_LIMIT', 5);
            $window = Env::int('CONTACT_RATE_WINDOW_MINUTES', 10);

            if ($repository->recentCountByIp($request->ip(), $window) >= $limit) {
                Response::fail(
                    'Çok fazla istek gönderdiniz. Lütfen birkaç dakika sonra tekrar deneyin.',
                    [],
                    429
                );
                return;
            }

            // 4) Kayit
            $id = $repository->create(
                [
                    'full_name' => $data['full_name'],
                    'email'     => $data['email'],
                    'phone'     => $data['phone'],
                    'message'   => $data['message'],
                ],
                $request->ip(),
                $request->userAgent()
            );

            Response::ok('Talebiniz alındı. Servis danışmanımız aynı gün içinde dönüş yapacak.', [
                'reference' => sprintf('KLB-%06d', $id),
            ]);
        } catch (RuntimeException $e) {
            // Veritabanina ulasilamadi
            error_log('[contact] ' . $e->getMessage());
            Response::fail('Şu anda talebinizi kaydedemiyoruz. Lütfen telefonla ulaşın.', [], 503);
        } catch (Throwable $e) {
            error_log('[contact] ' . $e->getMessage());
            Response::fail('Beklenmeyen bir hata oluştu.', [], 500);
        }
    }
}
