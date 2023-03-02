<?php

namespace Zeijaku\Cryptography;

interface EncryptInterface
{

    /**
     * @param $plaintext
     * @return mixed
     */
    public function encrypt($plaintext);

    /**
     * @param $ciphertext
     * @return mixed
     */
    public function decrypt($ciphertext);
}