<?php

namespace Zeijaku\Cryptography;

class Encrypt implements EncryptInterface
{
    protected $cipher;
    protected $mode;
    protected $key;

    protected $options = array(
        'iv_source' => MCRYPT_DEV_URANDOM
    );

    /**
     * @param $cipher
     * @param $mode
     * @param $key
     * @param array $options
     */
    public function __construct($cipher, $mode, $key, $options = array())
    {
        $this->cipher = $cipher;
        $this->mode = $mode;
        $this->key = $key;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param $plaintext
     * @return string
     */
    public function encrypt($plaintext)
    {
        $iv = $this->setIv();
        $ciphertext = mcrypt_encrypt($this->cipher, $this->key, $plaintext, $this->mode, $iv);
        $ciphertext = $iv . $ciphertext;

        return base64_encode($ciphertext);
    }

    /**
     * @param $ciphertext
     * @return string
     */
    public function decrypt($ciphertext)
    {
        $ciphertext = base64_decode($ciphertext);
        $iv = $this->getIv($ciphertext);
        $ciphertext = substr($ciphertext, strlen($iv));
        $plaintext = mcrypt_decrypt($this->cipher, $this->key, $ciphertext, $this->mode, $iv);
        $plaintext = rtrim($plaintext, "\0"); // remove null padding

        return $plaintext;
    }

    /**
     * @return string
     */
    protected function setIv()
    {
        $size = mcrypt_get_iv_size($this->cipher, $this->mode);
        return mcrypt_create_iv($size, $this->options['iv_source']);
    }

    /**
     * @param $payload
     * @return string
     */
    protected function getIv($payload)
    {
        $size = mcrypt_get_iv_size($this->cipher, $this->mode);
        return substr($payload, 0, $size);
    }
}