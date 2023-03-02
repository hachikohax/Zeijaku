<?php

namespace Zeijaku\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

class BaseCookieJar extends AbstractCookieJar
{
    /**
     * @param $key
     * @return mixed
     */
    function get($key)
    {
        return $this->request->cookies->get($key);
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     */
    function set($name, $value, $expire = 0, $path = '/', $domain = null, $secure = false, $httponly = false)
    {
        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        $this->response->headers->setCookie($cookie);
    }
}