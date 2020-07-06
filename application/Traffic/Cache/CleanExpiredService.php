<?php
namespace Traffic\Cache;

use Traffic\Service\AbstractService;

class CleanExpiredService extends AbstractService
{
    const EXT = '.doctrine';

    protected function _getIterator($path)
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    protected function _isDoctrineCache($name)
    {
        return strrpos($name, self::EXT) === (strlen($name) - strlen(self::EXT));
    }

    public function cleanCacheFolder($path)
    {
        if (!is_dir($path)) {
            return;
        }
        foreach ($this->_getIterator($path) as $name => $file) {
            if ($file->isDir()) {
                // Remove the intermediate directories which have been created to balance the tree. It only takes effect
                // if the directory is empty. If several caches share the same directory but with different file extensions,
                // the other ones are not removed.
                @rmdir($name);
            } else {
                $resource = fopen($name, "r");
                $lifetime = 1; // if no lifetime delete
                if (false !== ($line = fgets($resource))) {
                    $lifetime = (int) $line;
                }
                fclose($resource);
                if ($lifetime !== 0 && $lifetime < time()) {
                    @unlink($name);
                }
            }
        }

        return true;
    }
}