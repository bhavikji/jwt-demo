<?php
/**
 * Created by PhpStorm.
 * User: jaymin
 * Date: 9/5/17
 * Time: 8:45 AM
 */

namespace App\Common;

use Illuminate\Support\Facades\Crypt;

trait EncryptDecrypt
{
    protected function encryptText($text)
    {
        return Crypt::encryptString($text);
    }

    protected function decryptText($text)
    {
        return Crypt::decryptString($text);
    }
}