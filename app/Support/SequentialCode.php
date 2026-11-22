<?php

namespace App\Support;

class SequentialCode
{
    public static function next(string $prefix, ?string $lastCode): string
    {
        $number = $lastCode === null ? 1 : (int) substr($lastCode, -4) + 1;

        return $prefix.'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
