<?php

namespace App\Exceptions;

use RuntimeException;

class TokenException extends RuntimeException
{
    public function __construct(public readonly string $kode, string $pesan)
    {
        parent::__construct($pesan);
    }
}
