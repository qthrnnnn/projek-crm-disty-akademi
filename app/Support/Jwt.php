<?php

namespace App\Support;

use App\Exceptions\TokenException;

/* [MODUL 6] JWT (JSON Web Token) HS256. Ditulis sendiri secara murni menggunakan PHP (tanpa paket luar).
   Token = header.payload.signature (masing-masing base64url)
   - header   : {"alg":"HS256","typ":"JWT"}
   - payload  : data pengguna + iat (waktu dibuat) + exp (waktu kedaluwarsa)
   - signature: HMAC-SHA256(header.payload, rahasia)
*/
final class Jwt
{
    public static function buat(array $payload, string $rahasia, int $berlakuDetik): string
    {
        $sekarang = time();
        $header = self::b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES));
        $isi = self::b64(json_encode(
            [...$payload, 'iat' => $sekarang, 'exp' => $sekarang + $berlakuDetik],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ));

        return "{$header}.{$isi}.".self::b64(self::tandaTangan("{$header}.{$isi}", $rahasia));
    }

    /**
     * @throws TokenException
     */
    public static function periksa(string $token, string $rahasia): array
    {
        $bagian = explode('.', $token);
        if (count($bagian) !== 3) {
            throw new TokenException('TIDAK_VALID', 'Format token tidak valid');
        }
        [$header, $isi, $ttd] = $bagian;

        $dataHeader = json_decode(self::dekode($header) ?? '', true);
        $payload = json_decode(self::dekode($isi) ?? '', true);
        if (! is_array($dataHeader) || ! is_array($payload)) {
            throw new TokenException('TIDAK_VALID', 'Struktur token rusak');
        }

        // Hanya menerima algoritma HS256
        if (($dataHeader['alg'] ?? null) !== 'HS256') {
            throw new TokenException('TIDAK_VALID', 'Algoritma token tidak didukung');
        }

        $seharusnya = self::tandaTangan("{$header}.{$isi}", $rahasia);
        $diterima = self::dekode($ttd);
        if ($diterima === null || ! hash_equals($seharusnya, $diterima)) {
            throw new TokenException('TIDAK_VALID', 'Tanda tangan token tidak cocok');
        }

        if (! isset($payload['exp']) || ! is_int($payload['exp']) || $payload['exp'] <= time()) {
            throw new TokenException('KEDALUWARSA', 'Token sudah kedaluwarsa');
        }

        return $payload;
    }

    private static function tandaTangan(string $isi, string $rahasia): string
    {
        return hash_hmac('sha256', $isi, $rahasia, true);
    }

    private static function b64(string $nilai): string
    {
        return rtrim(strtr(base64_encode($nilai), '+/', '-_'), '=');
    }

    private static function dekode(string $nilai): ?string
    {
        $hasil = base64_decode(strtr($nilai, '-_', '+/').str_repeat('=', (4 - strlen($nilai) % 4) % 4), true);

        return $hasil === false ? null : $hasil;
    }
}
