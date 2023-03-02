<?php

namespace Zeijaku\Core\Providers;

use League\Container\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    protected $provides = [
        'v',
        'validator'
    ];

    public function register()
    {
        $this->getContainer()->singleton('v', 'Respect\Validation\Validator');
        $this->getContainer()->singleton('validator', 'Respect\Validation\Validator');
    }
}