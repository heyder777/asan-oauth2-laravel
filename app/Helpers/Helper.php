<?php

namespace App\Helpers;

class Helper
{
    public static function getAccessTokenPayload(string $accessToken)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $accessToken)[1]))), true);
    }
}
