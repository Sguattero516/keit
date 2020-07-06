<?php
namespace Traffic\CommandQueue\QueueStorage;

interface StorageInterface
{
    public function push($command);
    public function pop();
    public function count();
    public function isAvailable();
    public function clean();
    public function enableCompression();
}