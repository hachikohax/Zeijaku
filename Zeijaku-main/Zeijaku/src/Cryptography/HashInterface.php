<?php

namespace Zeijaku\Cryptography;

interface HashInterface
{

    /**
     * @param $data
     * @param null $algo
     * @return mixed
     */
    public function make($data, $algo = null);

    /**
     * @param $data
     * @param $hash
     * @return mixed
     */
    public function verify($data, $hash);

    /**
     * @param $data
     * @param null $algo
     * @return mixed
     */
    public function hmac($data, $algo = null);

    /**
     * @param $password
     * @return mixed
     */
    public function password($password);

    /**
     * @param $password
     * @param $hash
     * @return mixed
     */
    public function verify_pass($password, $hash);
}