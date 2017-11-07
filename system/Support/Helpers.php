<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

if (!function_exists('env')) {
    function env($name, $default = null)
    {
        return (!empty(getenv($name)) ? getenv($name) : $default);
    }
}

if (!function_exists('object_get')) {
    function object_get($object, $key, $default = null)
    {
        if (!isset($object) or !is_object($object)) {
            return $default;
        }

        $layers = explode('.', $key);
        $current = $object;
        foreach ($layers as $layer) {
            if (!property_exists($current, $layer)) {
                return $default;
            }

            $current = $current->{$layer};
        }

        return $current;
    }
}

if (! function_exists('is_true')) {
    /**
     * Helper function to check for a truthy object
     *
     * Borrowed from https://secure.php.net/manual/en/function.boolval.php#116547
     *
     * @param  mixed $val
     * @param  bool  $returnNull
     * @return bool
     */
    function is_true($val, $returnNull = false) : bool
    {
        $boolval = (is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val);
        return ($boolval === null && ! $returnNull ? false : $boolval);
    }
}
