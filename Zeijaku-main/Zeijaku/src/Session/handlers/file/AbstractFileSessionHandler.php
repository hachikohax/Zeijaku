<?php

namespace Zeijaku\Session\Handlers\File;

abstract class AbstractFileSessionHandler implements \SessionHandlerInterface
{
    protected $rootDir = '/tmp';
    protected $savePath;
    protected $config = array(
        'file_path'
    );

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        $this->savePath = $this->config['file_path'] . '/' . $savePath;
        if (! is_dir($this->savePath)) {
            mkdir($this->savePath, 0750);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param int|string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $file = $this->savePath . '/' . $sessionId;
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * @param int|string $maxlifetime
     */
    public function gc($maxlifetime)
    {
        foreach (glob($this->savePath . '/') as $file) {
            if (file_exists($file) && filemtime($file) + $maxlifetime < time()) {
                unlink($file);
            }
        }
    }

    /**
     * @param $delete
     * @return mixed
     */
    abstract function regen($delete);

    /**
     * @param string $sessionId
     * @return mixed
     */
    abstract function read($sessionId);

    /**
     * @param string $sessionId
     * @param string $data
     * @return mixed
     */
    abstract function write($sessionId, $data);
}