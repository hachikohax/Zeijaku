<?php

namespace Zeijaku\Session\Providers;

use League\Container\ServiceProvider;
use Zeijaku\Session\Handlers\File\BaseFileSessionHandler;

class BaseSessionManagerServiceProvider extends ServiceProvider
{
    protected $provides = [
        'SessionHandlerInterface',
        'session'
    ];

    public function register()
    {
        $this->getContainer()->singleton('SessionHandlerInterface', function () {
            return new BaseFileSessionHandler();
        });

        $this->getContainer()->singleton('session', 'Zeijaku\Session\Managers\BaseSessionManager')
            ->withArgument('SessionHandlerInterface');
    }
}