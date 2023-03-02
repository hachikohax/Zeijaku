<?php

namespace Zeijaku\Database\Providers;

use Zeijaku\Database\Compilers\SecureCompiler;
use League\Container\ServiceProvider;

class SecureDatabaseProvider extends ServiceProvider
{
    protected $provides = [
        'db',
        'database'
    ];

    public function register()
    {
        $connection = $this->getContainer()->get('dbh');
        $compiler = new SecureCompiler();

        $this->getContainer()->add('db', 'Zeijaku\Database\QueryBuilders\SecureQueryBuilder')
            ->withArgument($connection)
            ->withArgument($compiler);
    }
}