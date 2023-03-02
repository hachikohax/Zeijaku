<?php

namespace Zeijaku\Cryptography\Providers;

use League\Container\ServiceProvider;
use Zeijaku\Cryptography\Encrypt;
use Zeijaku\Cryptography\Hash;

class CryptographyServiceProvider extends ServiceProvider
{
    protected $provides = [
        'crypt',
        'hash',
        'Zeijaku\Cryptography\EncryptInterface',
        'Zeijaku\Cryptography\HashInterface',
    ];

    public function register()
    {
        $encryptConfig = $this->getContainer()->get('app')->getConfig('encryption');
        $hashConfig = $this->getContainer()->get('app')->getConfig('hashing');

        $this->getContainer()->singleton('Zeijaku\Cryptography\EncryptInterface', function () use ($encryptConfig) {
            return new Encrypt(
                $encryptConfig['cipher'],
                $encryptConfig['mode'],
                $encryptConfig['key'],
                $encryptConfig['options']
            );
        });
        $this->getContainer()->singleton('crypt', 'Zeijaku\Cryptography\EncryptInterface');

        $this->getContainer()->singleton('Zeijaku\Cryptography\HashInterface', function () use ($encryptConfig, $hashConfig) {
            return new Hash(
                $hashConfig['hash_algo'],
                $hashConfig['hmac_algo'],
                $hashConfig['pass_algo'],
                $encryptConfig['key']
            );
        });
        $this->getContainer()->singleton('hash', 'Zeijaku\Cryptography\HashInterface');
    }
}