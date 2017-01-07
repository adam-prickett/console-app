<?php

namespace App\Commands;

use System\Console\Command;

class ExampleCommand extends Command
{
    /**
     * Specifies the required options and arguments
     * @return array
     */
    public function required()
    {
        return [
            'options' => [
                ['timestamp', 't']
            ],
            'arguments' => 0,
        ];
    }

    /**
     * Provides the short description of this command for the command list
     * @return string
     */
    public function description()
    {
        return 'Example command - prints the date and time';
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

        print date('l, d F Y H:i', $timestamp);
    }
}
