<?php
namespace Traffic\CachedData\Storage;

interface StorageInterface
{
    /**
     * @param $key string
     * @param $data mixed
     * @return string|object
     */
    public function set($key, $data);
    /**
     * @param $key string
     * @return string|object|array
     */
    public function get($key);

    /**
     * @param $key string
     * @return void
     */
    public function delete($key);
    /**
     * @return string|object
     */
    public function deleteAll();
    /**
     * @param $key string
     * @return bool
     */
    public function exists($key);

    /**
     * @return int
     */
    public function size();

    /**
     * @return array
     */
    public function info();

    /**
     * @return null
     */
    public function commit();
}