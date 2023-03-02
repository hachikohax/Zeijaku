<?php

namespace Zeijaku\Database\Connectors;

use PDO;

abstract class AbstractConnector
{
    protected $options = array(
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false
    );

    protected $handler;

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_replace($this->options, $options) + $options;
    }

    /**
     * @param $dsn
     * @param $config
     * @return PDO
     */
    public function  createConnection($dsn, $config)
    {
        $username = $config['username'];
        $password = $config['password'];
        $this->setOptions($config['options']);

        try {
            $this->handler = new PDO($dsn, $username, $password, $this->getOptions());
        }
        catch (\PDOException $e) {
            echo "Connection Failed: {$e->getMessage()}";
        }

        return $this->handler;
    }
}