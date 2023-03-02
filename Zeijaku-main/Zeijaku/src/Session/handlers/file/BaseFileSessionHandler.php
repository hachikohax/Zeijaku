<?php

namespace Zeijaku\Session\Handlers\File;

class BaseFileSessionHandler extends AbstractFileSessionHandler
{
    /**
     * @param bool $delete
     */
    public function regen($delete = false)
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
            return unserialize(file_get_contents($file));
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
        return file_put_contents($file, serialize($data));
    }
}