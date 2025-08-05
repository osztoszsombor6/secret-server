<?php

namespace Service;

use DateTime;

class Utils
{
    /**
     * Function for retrieving Http headers.
     * @return array An associative array with header names as keys containing header values.
     */
    public static function getRequestHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }

    /**
     * Function for converting a given string to DateTime using the date format constant of the SecretDB class.
     * May be used on null string input in the case of secrets which do not expire based on time.
     *
     * @param string $str The string to convert.
     * @return ?DateTime The DateTime object created from the string, or null with null input.
     */
    public static function getDateTimeFromString($str): ?DateTime
    {
        if ($str != null) {
            return DateTime::createFromFormat(\Database\SecretDB::DB_DATE_FORMAT, $str);
        }
        return null;
    }

    public static function getUnqualifiedClassName($classname)
    {
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }
        return $classname;
    }
}
