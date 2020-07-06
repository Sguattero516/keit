<?php
namespace Traffic\LpToken\Storage;

interface StorageInterface
{
    /**
     * @return void
     */
    public function enableCompression();

    /**
     * @param $token string
     * @param $value string
     * @param $ttl int Seconds
     * @return mixed
     */
    public function set($token, $value, $ttl);

    /**
     * @param $token string
     * @return string
     */
    public function get($token);

    /**
     * @param $token string
     * @return void
     */
    public function delete($token);
}