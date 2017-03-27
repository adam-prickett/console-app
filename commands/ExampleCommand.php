<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace Commands;

use System\Log\Log;
use System\Console\Command;

class ExampleCommand extends Command
{
    protected $signature = 'example {--timestamp|-t}
                                    {format}
                                    {timezone?}';

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
        return <<<EOS
This is the help text for this command.
Here we can explain the various options available.

Options
---------

  -t/--timestamp    Provides the timestamp to use, defaults to current
  -q/--quiet        Silences output
EOS;
    }

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        $timestamp = $this->option(['timestamp', 't'], time());
        $date = date($this->argument('format'), $timestamp);

        $this->output($date);
    }
}
