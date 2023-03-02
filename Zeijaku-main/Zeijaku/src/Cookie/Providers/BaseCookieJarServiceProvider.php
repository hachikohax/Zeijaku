<?php

namespace Zeijaku\Cookie\Providers;

use League\Container\ServiceProvider;
use Zeijaku\Cookie\BaseCookieJar;

class BaseCookieJarServiceProvider extends ServiceProvider
{
    protected $provides = [
        'cookie'
    ];

    public function register()
    {
        $this->getContainer()->singleton('cookie', function () {
            $request = $this->getContainer()->get('request');
            $response = $this->getContainer()->get('response');
            return new BaseCookieJar($request, $response);
        });
    }
}