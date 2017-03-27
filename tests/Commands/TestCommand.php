<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace AppTests\Commands;

use System\Log\Log;
use System\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test    {--option1}
                                    {--option2|-o}
                                    {--option3?}
                                    {arg1}
                                    {arg2?}';

    /**
     * Setup the Command
     * @return void
     */
    public function setup()
    {
        //
    }

    /**
     * Provides the help text for this command
     * @return string
     */
    public function help()
    {
        return 'HELP-TEXT';
    }

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        print 'SUCCESS';
    }
}
