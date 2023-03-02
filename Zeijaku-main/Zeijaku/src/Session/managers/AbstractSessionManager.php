<?php

namespace Zeijaku\Session\Managers;

abstract class AbstractSessionManager
{
    protected $handler;
    protected $config = array(
        'ttl'               => 120,
        'match_ip'          => false,
        'netmask'           => '255.255.0.0',
        'match_user_agent'  => true,
    );

    /**
     * @param \SessionHandlerInterface $handler
     * @param $config
     */
    public function __construct(\SessionHandlerInterface $handler, $config)
    {
        $this->handler = $handler;
        $this->config = array_merge($this->config, $config);
        session_set_save_handler($this->handler);
    }

    /**
     * @return int
     */
    public function status()
    {
        return session_status();
    }

    /**
     * @return string
     */
    public function id()
    {
        return session_id();
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return $this->has($key) ? $_SESSION[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys($_SESSION);
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $_SESSION;
    }

    /**
     * @param null $ttl
     * @return bool
     */
    public function isExpired($ttl = null)
    {
        $ttl = is_null($ttl) ? $this->config['ttl'] : $ttl;
        $activity = $this->has('last_activity') ? $this->get('last_activity') : false;

        if ($activity !== false && time() - $activity > $ttl * 60) {
            return true;
        }

        $this->set('last_activity', time());

        return false;
    }

    /**
     * @return int
     */
    public function getIp()
    {
        return ip2long($_SERVER['REMOTE_ADDR']) & ip2long($this->config['netmask']);
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
       return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * @param null $ttl
     * @return bool
     */
    public function isValid($ttl = null)
    {
        $ttl = is_null($ttl) ? $this->config['ttl'] : $ttl;
        $ip = $this->config['match_ip'] ? $this->getIp() : null;
        $userAgent = $this->config['match_user_agent'] ? $this->getUserAgent() : null;

        if (!is_null($ip) || !is_null($userAgent)) {
            $hash = md5($ip.$userAgent);

            if (isset($_SESSION['fingerprint'])) {
                $fingerprint = $_SESSION['fingerprint'] === $hash;
            } else {
                $_SESSION['fingerprint'] = $hash;
                $fingerprint = true;
            }

            $ttl = is_null($ttl) ? $this->config['ttl'] : $ttl;
            return $this->isExpired($ttl) && $fingerprint;
        }

        return $this->isExpired($ttl);
    }

    /**
     * @return mixed
     */
    abstract function start();

    /**
     * @return mixed
     */
    abstract function regen();

    /**
     * @return mixed
     */
    abstract function destroy();
}