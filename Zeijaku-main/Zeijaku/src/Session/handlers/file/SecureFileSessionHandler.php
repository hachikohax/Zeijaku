<?php

namespace Zeijaku\Session\Handlers\File;

use Zeijaku\Cryptography\EncryptInterface;

class SecureFileSessionHandler extends AbstractFileSessionHandler
{
    protected $encrypter;
    protected $config = array(
        'cookie'            => 'PHPSESSID',
        'file_path'         => 'storage/sessions'
    );

    /**
     * @param EncryptInterface $encrypter
     * @param array $config
     */
    public function __construct(EncryptInterface $encrypter, $config = array())
    {
        $this->encrypter = $encrypter;
        $this->config = array_merge($this->config, $config);

        $cookieConfig = [
            'name'      => $this->config['cookie'],
            'lifetime'  => 0,
            'path'      => ini_get('session.cookie_path'),
            'domain'    => ini_get('session.cookie_domain'),
            'secure'    => isset($_SERVER['HTTPS']),
            'httponly'  => true
        ];

        session_name($cookieConfig['name']);

        session_set_cookie_params(
            $cookieConfig['lifetime'], $cookieConfig['path'],
            $cookieConfig['domain'], $cookieConfig['secure'],
            $cookieConfig['httponly']
        );
    }

    /**
     * @param bool $delete
     */
    public function regen($delete = true)
    {
        session_regenerate_id($delete);
    }

    /**
     * @param string $sessionId
     * @return bool|mixed
     */
    public function read($sessionId)
    {
        $file = $this->savePath . '/' . $sessionId;
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            return $this->encrypter->decrypt($data);
        }

        return false;
    }

    /**
     * @param string $sessionId
     * @param string $data
     * @return int
     */
    public function write($sessionId, $data)
    {
        $file = $this->savePath . '/' . $sessionId;
        $data = $this->encrypter->encrypt($data);
        return file_put_contents($file, serialize($data));
    }
}