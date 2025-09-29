<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies;

class CustomEncryptCookiesMiddleware extends EncryptCookies
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = ['access_token'];
}
