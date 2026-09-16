<?php
declare(strict_types=1);

namespace App\Support;

use App\Core\Env;
use Throwable;

/**
 * Yeni talep bildirimi.
 *
 * Form kaydi veritabanina yaziliyordu ama kimse haberdar olmuyordu; hizmete
 * acik bir sayfada bu, cevaplanmayan randevu demektir.
 *
 * Tasiyici .env ile secilir:
 *   log   (varsayilan) storage/logs/bildirimler.log dosyasina satir yazar.
 *                      SMTP kurulumu olmayan ortamda akisin calistigi
 *                      dogrulanabilir kalir.
 *   mail               PHP mail() ile gonderir.
 *
 * Bildirim gonderilemezse talep yine de kaydedilmistir; bu katman
 * hicbir kosulda istisna firlatmaz, hatayi error_log'a birakir.
 */
final class Notifier
{
    /**
     * @param array{full_name:string,email:string,phone:string,message:string} $data
     */
    public static function newRequest(array $data, string $reference): bool
    {
        $to = (string) Env::get('NOTIFY_TO', '');
        if ($to === '') {
            return false; // bildirim adresi tanimlanmamis, sessizce gec
        }

        $subject = sprintf('Yeni randevu talebi: %s (%s)', $data['full_name'], $reference);
        $body    = self::body($data, $reference);

        try {
            return match (strtolower((string) Env::get('NOTIFY_TRANSPORT', 'log'))) {
                'mail'  => self::viaMail($to, $subject, $body, $data['email']),
                default => self::viaLog($to, $subject, $body),
            };
        } catch (Throwable $e) {
            error_log('[notifier] ' . $e->getMessage());
            return false;
        }
    }

    /** @param array<string,string> $data */
    private static function body(array $data, string $reference): string
    {
        return implode("\n", [
            'Referans : ' . $reference,
            'Ad soyad : ' . $data['full_name'],
            'Telefon  : ' . $data['phone'],
            'E-posta  : ' . $data['email'],
            'Tarih    : ' . date('d.m.Y H:i'),
            '',
            'Mesaj:',
            $data['message'],
            '',
            '-- ',
            'Bu bildirim ' . Env::get('APP_NAME', 'site') . ' iletisim formundan olusturuldu.',
        ]);
    }

    private static function viaMail(string $to, string $subject, string $body, string $replyTo): bool
    {
        $from = (string) Env::get('NOTIFY_FROM', 'no-reply@localhost');

        $headers = [
            'From: ' . $from,
            'Reply-To: ' . $replyTo,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: PHP/' . PHP_VERSION,
        ];

        // Konu satirinda Turkce karakter: RFC 2047 base64 kodlamasi.
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        return mail($to, $encodedSubject, $body, implode("\r\n", $headers));
    }

    private static function viaLog(string $to, string $subject, string $body): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $entry = sprintf(
            "[%s] alici=%s konu=%s\n%s\n%s\n",
            date('c'),
            $to,
            $subject,
            $body,
            str_repeat('-', 60)
        );

        return file_put_contents($dir . '/bildirimler.log', $entry, FILE_APPEND | LOCK_EX) !== false;
    }
}
