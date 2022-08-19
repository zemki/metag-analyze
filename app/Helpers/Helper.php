<?php // Code within app\Helpers\Helper.php
namespace App\Helpers;

use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Helper
{
    /**
     * Generate random string with a given set of chars
     * @param        $length
     * @param string $keyspace
     * @return string
     * @throws Exception
     */
    public static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$%()=-*.,')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    /**
     * return extension of base64 file
     * @param String $uri base64 file uri
     * @return String      image extension
     */
    public static function extension($uri)
    {
        $img = explode(',', $uri);
        $ini = substr($img[0], 11);
        $type = explode(';', $ini);
        return $type[0];
    }

    /**
     * @param $string
     * @param $start
     * @param $end
     * @return bool|string
     */
    public static function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini === 0 || $ini === false) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        if (strpos($string, $end, $ini) === false) {
            $len = strlen($string) - 1;
        }
        return substr($string, $ini, $len);
    }

    /**
     * Search for an element in array recursively
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function in_array_recursive($needle, $haystack)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($haystack));
        foreach ($iterator as $element) {
            if ($element === $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param       $myArray
     * @param       $MAXDEPTH
     * @param int   $depth
     * @param array $arrayKeys
     * @return array
     */
    public static function array_keys_recursive($myArray, $arrayKeys = array(), $MAXDEPTH = INF, $depth = 0)
    {
        if ($depth < $MAXDEPTH) {
            $depth++;
            $keys = array_keys($myArray);
            foreach ($keys as $key) {
                if (is_array($myArray[$key])) {
                    $arrayKeys[$key] = Helper::array_keys_recursive($myArray[$key], $MAXDEPTH, $depth);
                }
            }
        }
        return $arrayKeys;
    }

    /**
     * @param $array
     * @return array
     */
    public static function multiexplode($delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }
}
