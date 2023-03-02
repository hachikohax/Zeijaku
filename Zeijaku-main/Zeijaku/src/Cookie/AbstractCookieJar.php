<?php

namespace Zeijaku\Cookie;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCookieJar
{
    protected $request;
    protected $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param $key
     * @return mixed
     */
    abstract function get($key);

    /**
     * @param $name
     * @param $value
     * @param $expire
     * @param $path
     * @param $domain
     * @param $secure
     * @param $httpOnly
     * @return mixed
     */
    abstract function set($name, $value, $expire, $path, $domain, $secure, $httpOnly);

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->request->cookies->has($key);
    }

    /**
     * @return array
     */
    public function keys()
    {
        return $this->request->cookies->keys();
    }

    /**
     * @return array
     */
    public function all()
    {
        $cookies = $this->request->cookies->all();

        foreach ($cookies as $key => $value) {
            $cookies[$key] = $this->get($key);
        }

        return $cookies;
    }

    /**
     * @param $key
     */
    public function clear($key)
    {
        $this->response->headers->clearCookie($key);
    }
}