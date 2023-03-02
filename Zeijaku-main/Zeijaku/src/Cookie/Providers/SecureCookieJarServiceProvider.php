<?php

namespace Zeijaku\Cookie\Providers;

use League\Container\ServiceProvider;
use Zeijaku\Cookie\SecureCookieJar;

class SecureCookieJarServiceProvider extends ServiceProvider
{
    protected $provides = [
        'cookie'
    ];

    public function register()
    {
        $config = $this->getContainer()->get('app')->getConfig('cookie');

        $this->getContainer()->singleton('cookie', function () use ($config) {
            $request = $this->getContainer()->get('request');
            $response = $this->getContainer()->get('response');
            $encrypter = $this->getContainer()->get('crypt');
            $hasher = $this->getContainer()->get('hash');
            return new SecureCookieJar($request, $response, $encrypter, $hasher, $config);
        });
    }
}