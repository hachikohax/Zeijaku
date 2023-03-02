<?php

namespace Zeijaku\Database\Connectors;

class MySqlConnector extends AbstractConnector implements ConnectorInterface
{
    /**
     * @param array $config
     * @return \PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);
        $connection = $this->createConnection($dsn, $config);

        return $connection;
    }

    /**
     * @param $config
     * @return string
     */
    public function getDsn($config)
    {
        $host = $config['host'];
        $port = isset($config['port']) ? $config['port'] : null;
        $dbname = $config['dbname'];

        return isset($config['port'])
            ? "mysql:host={$host};port={$port};dbname={$dbname}"
            : "mysql:host={$host};dbname={$dbname}";
    }
}