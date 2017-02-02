<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@ampersa.co.uk>
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
