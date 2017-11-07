<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace Commands;

use System\Log\Log;
use System\Console\Command;

class ExampleCommand extends Command
{
    protected $signature = 'example (An example command) {--timestamp : The timestamp to decode}
                                                         {format : The format to display the date/time in}
                                                         {timezone : The timezone to use for timestamp decoding?}';

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        $timestamp = $this->option('timestamp', time());
        $date = date($this->argument('format'), $timestamp);

        $this->output($date);
    }
}
