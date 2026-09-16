<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HTTP bayt araligi (Range) destekli statik dosya sunucusu.
 *
 * NEDEN VAR:
 * PHP'nin dahili gelistirme sunucusu Range isteklerine 206 yerine 200 doner.
 * Tarayici bu durumda videoyu "sarilamaz" (seekable = 0) olarak isaretler ve
 * video.currentTime atamalarini sessizce yok sayar. Scroll ile surulen video
 * bolumu de bu yuzden donuk kalir.
 *
 * Uretimde Apache/nginx statik dosyalari kendi sunar ve Range'i destekler,
 * yani bu sinif oraya hic ugramaz. Ama "npm run serve" ile projeyi acan
 * herkesin ozelligi calisir halde gormesi icin gerekli.
 */
final class FileServer
{
    private const TIPLER = [
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'svg'  => 'image/svg+xml',
        'css'  => 'text/css; charset=utf-8',
        'js'   => 'text/javascript; charset=utf-8',
        'ico'  => 'image/x-icon',
        'woff2'=> 'font/woff2',
    ];

    /**
     * Istegi karsiladiysa true doner; false donerse yonlendirici devam eder.
     */
    public static function tryServe(string $publicDir, string $path): bool
    {
        if (!str_starts_with($path, '/assets/')) {
            return false;
        }

        // Dizin disina cikma denemelerine karsi gercek yolu dogrula
        $aday = realpath($publicDir . $path);
        $kok  = realpath($publicDir);

        if ($aday === false || $kok === false || !str_starts_with($aday, $kok) || !is_file($aday)) {
            return false;
        }

        $uzanti = strtolower(pathinfo($aday, PATHINFO_EXTENSION));
        $boyut  = (int) filesize($aday);
        $tip    = self::TIPLER[$uzanti] ?? 'application/octet-stream';
        $etag   = '"' . md5($aday . $boyut . (string) filemtime($aday)) . '"';

        header('Content-Type: ' . $tip);
        header('Accept-Ranges: bytes');
        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');

        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            return true;
        }

        [$bas, $son] = self::araligiCoz($_SERVER['HTTP_RANGE'] ?? '', $boyut);

        if ($bas === null) {
            // Aralik istendi ama gecersiz
            http_response_code(416);
            header('Content-Range: bytes */' . $boyut);
            return true;
        }

        $kismi = ($bas !== 0 || $son !== $boyut - 1);

        if ($kismi) {
            http_response_code(206);
            header(sprintf('Content-Range: bytes %d-%d/%d', $bas, $son, $boyut));
        }

        header('Content-Length: ' . (string) ($son - $bas + 1));

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
            return true;
        }

        self::gonder($aday, $bas, $son);
        return true;
    }

    /**
     * @return array{0:int|null,1:int} Gecersiz aralikta [null, 0] doner.
     */
    private static function araligiCoz(string $header, int $boyut): array
    {
        if ($header === '' || !preg_match('/^bytes=(\d*)-(\d*)$/', trim($header), $m)) {
            return [0, $boyut - 1];   // aralik istenmemis: tamami
        }

        [, $baslangic, $bitis] = $m;

        if ($baslangic === '' && $bitis === '') {
            return [null, 0];
        }

        if ($baslangic === '') {
            // "bytes=-500" : son 500 bayt
            $uzunluk = (int) $bitis;
            if ($uzunluk <= 0) {
                return [null, 0];
            }
            $bas = max(0, $boyut - $uzunluk);
            return [$bas, $boyut - 1];
        }

        $bas = (int) $baslangic;
        $son = $bitis === '' ? $boyut - 1 : (int) $bitis;
        $son = min($son, $boyut - 1);

        if ($bas > $son || $bas >= $boyut) {
            return [null, 0];
        }

        return [$bas, $son];
    }

    private static function gonder(string $dosya, int $bas, int $son): void
    {
        $tampon = 256 * 1024;
        $fp = fopen($dosya, 'rb');

        if ($fp === false) {
            return;
        }

        fseek($fp, $bas);
        $kalan = $son - $bas + 1;

        while ($kalan > 0 && !feof($fp)) {
            $parca = fread($fp, (int) min($tampon, $kalan));
            if ($parca === false) {
                break;
            }
            echo $parca;
            $kalan -= strlen($parca);
            flush();
        }

        fclose($fp);
    }
}
