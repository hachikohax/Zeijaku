<?php

namespace Zeijaku\Core\Providers;

use League\Container\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MonologServiceProvider extends ServiceProvider
{
    protected $provides = [
        'log'
    ];

    public function register()
    {
        $storage_path = $this->getContainer()->get('app')->storage_path();

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler($storage_path.'logs/notice.log', Logger::NOTICE));
        $this->getContainer()->singleton('log', $log);
    }
}