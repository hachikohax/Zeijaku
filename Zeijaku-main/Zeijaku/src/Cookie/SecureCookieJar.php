<?php

namespace Zeijaku\Cookie;

use Zeijaku\Cryptography\EncryptInterface;
use Zeijaku\Cryptography\HashInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureCookieJar extends AbstractCookieJar
{
    protected $encrypter;
    protected $hasher;

    protected $config = array(
        'encrypt'     => true,
        'sign'        => true,
        'sign_method' => 'hmac'
    );

    /**
     * @param Request $request
     * @param Response $response
     * @param EncryptInterface $encrypter
     * @param HashInterface $hasher
     * @param array $config
     */
    public function __construct(Request $request, Response $response, EncryptInterface $encrypter, HashInterface $hasher, $config = array())
    {
        parent::__construct($request, $response);
        $this->encrypter = $encrypter;
        $this->hasher = $hasher;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param $key
     * @return array|bool|mixed
     */
    function get($key)
    {
        $cookie = $this->request->cookies->get($key);

        if ($this->config['sign']) {
            if ($this->validate($cookie)) {
                $cookie = explode('--', $cookie);
                $cookie = $cookie[0];
            } else {
                return false;
            }
        }

        if ($this->config['encrypt']) {
            $cookie = $this->encrypter->decrypt($cookie);
        }

        return $cookie;
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
    function set($name, $value, $expire = 0, $path = '/', $domain = null, $secure = false, $httponly = true)
    {
        if ($this->config['encrypt']) {
            $value = $this->encrypter->encrypt($value);
        }

        if ($this->config['sign']) {
            if ($this->config['sign_method'] === 'hash') {
                $value = $value.'--'.$this->hasher->make($value);
            } else {
                $value = $value.'--'.$this->hasher->hmac($value);
            }
        }

        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        $this->response->headers->setCookie($cookie);
    }

    /**
     * @param $cookie
     * @return bool
     */
    protected function validate($cookie)
    {
        $parts = explode('--', $cookie);

        if ($this->config['sign_method'] === 'hash') {
            if (count($parts) !== 2 || $cookie !== $parts[0].'--'.$this->hasher->make($parts[0])) {
                return false;
            }
        } else {
            if (count($parts) !== 2 || $cookie !== $parts[0].'--'.$this->hasher->hmac($parts[0])) {
                return false;
            }
        }

        return true;
    }
}