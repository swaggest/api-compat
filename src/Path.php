<?php

namespace Swaggest\ApiCompat;


use Swaggest\JsonDiff\JsonPointer;

class Path
{
    public static function fitsPattern($path, $pattern)
    {
        $path = explode('/', $path);
        $pattern = explode('/', $pattern);

        foreach ($path as $i => $item) {
            if (!isset($pattern[$i])) {
                return false;
            }
            $pitem = $pattern[$i];
            if ($pitem === '...') {
                return true;
            }
            if (($pitem === '*') || $pitem === $item) {
                continue;
            } else {
                return false;
            }
        }
        if (count($pattern) > count($path)) {
            return false;
        }
        return true;
    }

    public static function quoteUrldecode($path)
    {
        $path = JsonPointer::splitPath($path);
        foreach ($path as &$item) {
            if ($item !== $u = urlencode($item)) {
                $item = "'" . $item . "'";
            }
        }
        return '#/' . implode('/', $path);
    }
}