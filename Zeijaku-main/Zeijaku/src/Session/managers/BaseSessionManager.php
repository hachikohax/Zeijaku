<?php

namespace Zeijaku\Session\Managers;

class BaseSessionManager extends AbstractSessionManager
{
    protected $config = [];

    public function __construct(\SessionHandlerInterface $handler, $config = array())
    {
        $this->config = array_merge($this->config, $config);
        parent::__construct($handler, $this->config);
    }

    public function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            return session_start();
        }

        return false;
    }

    public function regen($delete = false)
    {
        return session_regenerate_id($delete);
    }

    public function destroy()
    {
        return session_destroy();
    }
}