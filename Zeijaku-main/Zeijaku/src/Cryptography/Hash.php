<?php

namespace Zeijaku\Cryptography;

class Hash implements HashInterface
{
    protected $hash_algo;
    protected $hmac_algo;
    protected $pass_algo;
    protected $key;

    /**
     * @param $hash_algo
     * @param $hmac_algo
     * @param $pass_algo
     * @param $key
     */
    public function __construct($hash_algo, $hmac_algo, $pass_algo, $key)
    {
        $this->hash_algo = $hash_algo;
        $this->hmac_algo = $hmac_algo;
        $this->pass_algo = $pass_algo;
        $this->key = $key;
    }

    /**
     * @param $data
     * @param null $algo
     * @return string
     */
    public function make($data, $algo = null)
    {
        $algo = is_null($algo) ? $this->hash_algo : $algo;

        return hash($algo, $data);
    }

    /**
     * @param $data
     * @param $hash
     * @return bool
     */
    public function verify($data, $hash)
    {
        return $this->make($data) === $hash;
    }

    /**
     * @param $data
     * @param null $algo
     * @return string
     */
    public function hmac($data, $algo = null)
    {
        $algo = is_null($algo) ? $this->hmac_algo : $algo;

        return hash_hmac($algo, $data, $this->key);
    }

    /**
     * @param $password
     * @return bool|false|string
     */
    public function password($password)
    {
        if ($this->pass_algo !== PASSWORD_BCRYPT && $this->pass_algo !== PASSWORD_DEFAULT) {
            return hash($this->pass_algo, $password);
        }

        return password_hash($password, $this->pass_algo);
    }

    /**
     * @param $password
     * @param $hash
     * @return bool
     */
    public function verify_pass($password, $hash)
    {
        if ($this->pass_algo !== PASSWORD_BCRYPT && $this->pass_algo !== PASSWORD_DEFAULT) {
            return $this->verify($this->make($password, $this->pass_algo), $password);
        }

        return password_verify($password, $hash);
    }
}