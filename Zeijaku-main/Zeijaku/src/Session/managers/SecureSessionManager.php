<?php

namespace Zeijaku\Session\Managers;

class SecureSessionManager extends AbstractSessionManager
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
            session_start();
            return (mt_rand(0, $this->config['regen_odds']) === 0) ? $this->regen() : true;
        }

        return false;
    }

    public function regen($delete = true)
    {
        return session_regenerate_id($delete);
    }

    public function destroy()
    {
        $this->start();
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
        return session_destroy();
    }
}