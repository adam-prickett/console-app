<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
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

        if (!ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', '1');
        }
    }
}
