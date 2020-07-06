<?php
namespace Traffic\Tools;

/**
 * TODO избавляемся от этого класса, выносим все функции в более подходящие места
 */
class Tools
{
    public static $_units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

    // TODO методы для работы с CIDR используются только в UserBotsService, вот туда и надо убрать их
    public static function isValidCIDR($cidr)
    {
        if (!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\/[0-9]{1,2})?$/", $cidr)) {
            return false;
        }
        $parts = explode("/", $cidr);
        $ip = $parts[0];
        $netmask = $parts[1];
        $octets = explode(".", $ip);
        foreach ($octets as $octet) {
            if ($octet > 255) {
                return false;
            }
        }

        $mustCidr = false; // TODO проверить, нужно ли это

        if ((($netmask != "") && ($netmask > 32) && !$mustCidr) || (($netmask == "" || $netmask > 32) && $mustCidr)) {
            return false;
        }
        return true;
    }

    public static function ipInCIDR($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        $ipc = explode('.', $subnet);
        foreach ($ipc as &$v) {
            $v = str_pad(decbin($v), 8, '0', STR_PAD_LEFT);
        }
        $ipc = substr(join('', $ipc), 0, $mask);
        $ipu = explode('.', $ip);
        foreach ($ipu as &$v) {
            $v = str_pad(decbin($v), 8, '0', STR_PAD_LEFT);
        }
        $ipu = substr(join('', $ipu), 0, $mask);
        return $ipu == $ipc;
    }

    public static function CIDRToRange($cidr)
    {
        $range = array();
        $cidr = explode('/', $cidr);
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
        return $range;
    }

    public static function ip2long($ip, $unicode = true)
    {
        $key = (string)$ip;
        if (!isset($cache[$key])) {
            if ($unicode) {
                $cache[$key] = sprintf('%u', ip2long($ip));
            } else {
                $cache[$key] = ip2long($ip);
            }
        }
        return $cache[$key];
    }

    public static function ipInInterval($ip, $interval)
    {
        list($from, $to) = explode('-', $interval);
        return (Tools::ip2long(trim($from)) <= Tools::ip2long($ip))
        && (Tools::ip2long($ip) <= Tools::ip2long(
            trim($to)
        ));
    }

    public static function ipInMask($ip, $mask)
    {
        $split_it = explode('.', $ip);
        $ip = "1" . sprintf("%03d", $split_it[0]) .
            sprintf("%03d", $split_it[1]) . sprintf("%03d", $split_it[2]) .
            sprintf("%03d", $split_it[3]);

        $line = chop($mask);

        $line = str_replace("x", "*", $line);
        $line = preg_replace("|[A-Za-z#/]|", "", $line);
        $max = $line;
        $min = $line;
        if (strpos($line, "*", 0) <> "") {
            $max = str_replace("*", "999", $line);
            $min = str_replace("*", "000", $line);
        }
        if (strpos($line, "?", 0) <> "") {
            $max = str_replace("?", "9", $line);
            $min = str_replace("?", "0", $line);
        }
        if ($max == "") {
            return false;
        }
        if (strpos($max, " - ", 0) <> "") {
            $split_it = explode(' - ', $max);
            if (!preg_match("|\d{1,3}\.|", $split_it[1])) {
                $max = $split_it[0];
            } else {
                $max = $split_it[1];
            }
        }
        if (strpos($min, " - ", 0) <> "") {
            $split_it = explode(' - ', $min);
            $min = $split_it[0];
        }
        $split_it = explode('.', $max);
        for ($i = 0; $i < 4; $i++) {
            if ($i == 0) {
                $max = 1;
            }

            if (strpos($split_it[$i], "-", 0) <> "") {
                $another_split = explode('-', $split_it[$i]);
                $split_it[$i] = $another_split[1];
            }
            $max .= sprintf("%03d", $split_it[$i]);
        }

        $split_it = explode('.', $min);
        for ($i = 0; $i < 4; $i++) {
            if ($i == 0) {
                $min = 1;
            };
            if (strpos($split_it[$i], "-", 0) <> "") {
                $another_split = explode('-', $split_it[$i]);
                $split_it[$i] = $another_split[0];
            }
            $min .= sprintf("%03d", $split_it[$i]);
        }
        if (($ip <= $max) && ($ip >= $min)) {
            return true;
        }
    }

    public static function getFolderSize($path)
    {
        $total_size = 0;
        if (!file_exists($path)) {
            return 0;
        }
        $files = scandir($path);
        $cleanPath = rtrim($path, '/') . '/';

        if (is_array($files)) {
            foreach ($files as $t) {
                if ($t <> "." && $t <> "..") {
                    $currentFile = $cleanPath . $t;
                    if (is_dir($currentFile)) {
                        $size = self::getFolderSize($currentFile);
                        $total_size += $size;
                    } else {
                        $size = filesize($currentFile);
                        $total_size += $size;
                    }
                }
            }
        }

        return $total_size;
    }

    public static function sizeFormat($size)
    {
        if (!$size) {
            return $size;
        }

        $mod = 1024;

        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }

        $endIndex = strpos($size, ".") + 3;

        return substr($size, 0, $endIndex) . ' ' . self::$_units[$i];
    }

    public static function sanitizeFilename($filename)
    {
        $specialChars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*",
                              "(", ")", "|", "~", "`", "!", "{", "}");
        $filename = str_replace($specialChars, '', $filename);
        $filename = preg_replace('/[\s-]+/', '-', $filename);
        $filename = trim($filename, '.-_');
        return $filename;
    }

    public static function generateRandomString($length = 6)
    {
        $keyspace = '123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[rand(0, $max)];
        }
        return $str;
    }


    public static function translit($str)
    {
        $tbl = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'c', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
            'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'ы' => 'i', 'э' => 'e', 'А' => 'A',
            'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ж' => 'G', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Ы' => 'I', 'Э' => 'E', 'ё' => "yo", 'х' => "h",
            'ц' => "ts", 'ч' => "ch", 'ш' => "sh", 'щ' => "shch", 'ъ' => "", 'ь' => "", 'ю' => "yu", 'я' => "ya",
            'Ё' => "YO", 'Х' => "H", 'Ц' => "TS", 'Ч' => "CH", 'Ш' => "SH", 'Щ' => "SHCH", 'Ъ' => "", 'Ь' => "",
            'Ю' => "YU", 'Я' => "YA", 'і' => 'i', 'ї' => 'ji', 'І' => 'I', 'Ї' => "Ji", '\'' => ''
        );
        return strtr($str, $tbl);
    }

    public static function percent($value, $total)
    {
        if ($value > 0 && $total > 0) {
            $result = $value / $total * 100;
        } else {
            $result = 0;
        }
        return round($result, 1);
    }

    public static function sortCollection($items, $key)
    {
        $func = function ($item1, $item2) use ($key) {
            if ($item1[$key] == $item2[$key]) {
                return 0;
            }
            return $item1[$key] < $item2[$key] ? -1 : 1;
        };

        usort($items, $func);
        return $items;
    }

    public static function toCamelCase($str, $firstLower = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
        if ($firstLower) {
            $str = lcfirst($str);
        }
        return $str;
    }

    public static function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);

        $firstToLower = function($c) {
            return "_" . strtolower($c[1]);
        };

        $newStr = preg_replace_callback('/([A-Z0-9])/', $firstToLower , $str);

        return $newStr;
    }

    public static function demodulize($className)
    {
        return preg_replace('/.+\\\/', '', $className);
    }

    public static function humanize($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    public static function utf8ize($d)
    {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = static::utf8ize($v);
            }
        } else if (is_string($d) && !mb_detect_encoding($d, array('UTF-8'), TRUE)) {
            if (mb_detect_encoding($d, array('WINDOWS-1251'), TRUE)) {
                return mb_convert_encoding($d, 'UTF-8', 'WINDOWS-1251');
            }
            return mb_convert_encoding($d, 'UTF-8');
        }
        return $d;
    }

    public static function deleteDirectory($dir)
    {
        if (is_dir($dir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $name => $file) {
                if ($file->isDir()) {
                    rmdir($name);
                } else {
                    unlink($name);
                }
            }
            rmdir($dir);
        }
    }

    // mv sourceDir/* targetDir/
    public static function moveFilesFromDirectory($sourceDir, $targetDir)
    {
        $files = new \DirectoryIterator($sourceDir);
        foreach ($files as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            $fname = $fileinfo->getFilename();
            rename($sourceDir . '/' . $fname, $targetDir . '/' . $fname);
        }
    }

    public static function isRootUser()
    {
        if (function_exists('posix_getuid') && posix_getuid() == 0) {
            return true;
        }
        return false;
    }
}