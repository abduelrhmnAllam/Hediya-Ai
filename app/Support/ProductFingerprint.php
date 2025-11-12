<?php

namespace App\Support;

class ProductFingerprint
{
    public static function make(?string $brandName, ?string $productName): string
    {
        $brand = mb_strtolower(trim((string)$brandName));
        $name  = mb_strtolower(trim((string)$productName));
        return sha1($brand.'|'.$name);
    }
}
