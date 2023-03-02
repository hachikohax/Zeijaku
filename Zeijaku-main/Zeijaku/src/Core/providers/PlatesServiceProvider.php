<?php

namespace Zeijaku\Core\Providers;

use League\Container\ServiceProvider;
use League\Plates\Engine;

class PlatesServiceProvider extends ServiceProvider
{
    protected $provides = [
        'view'
    ];

    public function register()
    {
        $this->getContainer()->singleton('view', new Engine('../app/views'));
    }
}