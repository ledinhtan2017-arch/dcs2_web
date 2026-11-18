<?php
namespace App\Models;

class BankTransfer
{
    public static function details(): array
    {
        return [
            'bank' => (string) \App\Config\env('BANK_NAME', ''),
            'account' => (string) \App\Config\env('BANK_ACCOUNT_NUMBER', ''),
            'holder' => (string) \App\Config\env('BANK_ACCOUNT_NAME', ''),
        ];
    }

    public static function available(): bool
    {
        return !in_array('', self::details(), true);
    }
}
