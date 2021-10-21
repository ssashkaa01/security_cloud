<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityCloud extends Model
{
    public $timestamps = false;

    protected $table = 'security_cloud';

    protected $guarded = ['id'];

    public static function GenerateString($length = 8): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    public static function Encrypt($data, $password): object
    {
        $iv = self::GenerateString(16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc-hmac-sha256', $password, null, $iv);

        return (object)[
            'iv' => $iv,
            'data' => $encrypted
        ];
    }

    public static function Decrypt($encrypted, $password, $iv)
    {
        return openssl_decrypt($encrypted, 'aes-256-cbc-hmac-sha256', $password, null, $iv);
    }
}
