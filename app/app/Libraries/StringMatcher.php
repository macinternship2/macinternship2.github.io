<?php

namespace App\Libraries;

/*
    StringMatcher is very similar to
    importers/utils/import_helpers/string_matcher.py
    but written in PHP
*/

class StringMatcher
{
    public function __construct($path_prefix)
    {
        $this->names = [];
        $this->not_names = [];
        $this->name_regex = [];
        $this->not_name_regex = [];
        $filename = $path_prefix . '_names.txt';
        if (file_exists($filename)) {
            $this->names = StringMatcher::loadLinesFromFile($filename);
        }
        $filename = $path_prefix . '_name_regex.txt';
        if (file_exists($filename)) {
            $this->name_regex = StringMatcher::sanitizeAllRegex(StringMatcher::loadLinesFromFile($filename));
        }
        $filename = $path_prefix . '_not_name_regex.txt';
        if (file_exists($filename)) {
            $this->not_name_regex = StringMatcher::sanitizeAllRegex(StringMatcher::loadLinesFromFile($filename));
        }
        $filename = $path_prefix . '_not_names.txt';
        if (file_exists($filename)) {
            $this->not_names = StringMatcher::stripAll(StringMatcher::loadLinesFromFile($filename));
        }
    }

    private static function loadLinesFromFile($filename)
    {
        return explode("\n", file_get_contents($filename));
    }

    public static function singleSpace($s)
    {
        return preg_replace('/\s+/', ' ', $s);
    }

    private static function stripAll($list1)
    {
        return array_map(function ($s) {
                return strtolower(StringMatcher::singleSpace($s));
        },
            $list1);
    }

    public static function sanitizeRegex($regex1)
    {
        $regex1 = strtolower(preg_replace("/\r|\n/", "", $regex1));
        if (strpos($regex1, '$') !== false) {
            $regex1 = rtrim($regex1);
        }
        if (strpos($regex1, '^') !== false) {
            $regex1 = ltrim($regex1);
        }

        return '/'.$regex1.'/';
    }
        
    private static function sanitizeAllRegex($regexArray1)
    {
        return array_map('self::sanitizeRegex', $regexArray1);
    }

    private static function regexMatchesAny($s, $regexArray)
    {
        $result = false;
        foreach ($regexArray as $reg) {
            if (preg_match($reg, $s)) {
                $result = true;
            }
        }
        return $result;
    }
    
    public function appliesToName($location_name)
    {
        $location_name = StringMatcher::singleSpace(strtolower(trim($location_name)));
        if (in_array($location_name, $this->not_names)) {
            return false;
        }

        if (in_array($location_name, $this->names)) {
            return true;
        }

        if ($this->not_name_regex) {
            if (StringMatcher::regexMatchesAny($location_name, $this->not_name_regex)) {
                return false;
            }
        }

        if ($this->name_regex) {
            if (StringMatcher::regexMatchesAny($location_name, $this->name_regex)) {
                return true;
            }
        }

        return false;
    }
}
