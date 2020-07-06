<?php
class Migration_20190514145706_ConvertOldCustomFilters extends Migration
{
    const DESCRIPTION_RU = 'Адаптация старых пользовательских фильтров';

    const DESCRIPTION_EN = 'Adapt old user filters';

    private static $_path = ROOT . '/application/filters';

    public static function setPath($path)
    {
        static::$_path = $path;
    }

    public static function up()
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(static::$_path)) as $filename) {
            if ($filename->isDir() || strstr($filename, 'example.php') || !strstr($filename, '.php')) {
                continue;
            }
            $code = file_get_contents($filename);
            copy($filename, str_replace('.php', '.txt', $filename).'_copy_' . time());
            $code = str_replace('Component\StreamFilters\Model\StreamFilter', 'Traffic\Model\StreamFilter', $code);
            file_put_contents($filename, $code);
        }

    }
}