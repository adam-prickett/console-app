<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System\Application;

class Bootstrap
{
    /**
     * Run the bootstrap process
     * @return void
     */
    public static function run()
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

        mb_internal_encoding('UTF-8');
    }
}
