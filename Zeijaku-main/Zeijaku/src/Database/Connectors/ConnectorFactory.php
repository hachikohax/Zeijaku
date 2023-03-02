<?php

namespace Zeijaku\Database\Connectors;

class ConnectorFactory
{

    /**
     * @param $driver
     * @return MySqlConnector
     */
    public function make($driver)
    {
        switch ($driver) {
            case "mysql":
                return new MySqlConnector;
//            case 'pgsql':
//                return new PostgreSqlConnector;
//
//            case 'sqlite':
//                return new SqLiteConnector;
        }

        throw new \InvalidArgumentException("Unsupported Driver [{$driver}]");
    }
}