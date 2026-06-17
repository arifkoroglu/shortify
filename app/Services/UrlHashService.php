<?php

namespace App\Services;

class UrlHashService
{
    private const ALLOWED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const BASE = 62;

    /**
     * Veritabanı ID'sini (Integer) alıp 6 karakterli Base62 string'e dönüştürür.
     * (Gerekirse soluna '0' ekleyerek uzunluğu sabitleyebiliriz)
     */
    public function encode(int $id): string
    {
        if ($id === 0) {
            return str_repeat(self::ALLOWED_CHARS[0], 6);
        }

        $code = '';

        while ($id > 0) {
            $remainder = $id % self::BASE;
            $code = self::ALLOWED_CHARS[$remainder] . $code;
            $id = (int) ($id / self::BASE);
        }

        // Kodun her zaman standart 6 karakter uzunluğunda olmasını garanti edelim.
        // Eğer üretilen kod 6 karakterden kısaysa, sol tarafını alfabenin ilk karakteri (0) ile doldurur.
        return str_pad($code, 6, self::ALLOWED_CHARS[0], STR_PAD_LEFT);
    }

    /**
     * 6 karakterli Base62 string kodu alıp geri orijinal Veritabanı ID'sine (Integer) çevirir.
     */
    public function decode(string $code): int
    {
        // Sol taraftaki dolgu karakterlerini temizleyelim
        $code = ltrim($code, self::ALLOWED_CHARS[0]);
        
        if ($code === '') {
            return 0;
        }

        $id = 0;
        $length = strlen($code);

        for ($i = 0; $i < $length; $i++) {
            $char = $code[$i];
            $position = strpos(self::ALLOWED_CHARS, $char);

            if ($position === false) {
                throw new \InvalidArgumentException("Geçersiz karakter algılandı: {$char}");
            }

            // Basamak değerini hesaplayarak ID'yi geri topluyoruz
            $id = ($id * self::BASE) + $position;
        }

        return $id;
    }
}