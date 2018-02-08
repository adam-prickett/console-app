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
use System\Support\Bag;
use System\Console\Command;

class Injection2Command extends Command
{
    protected $signature = 'inject2 {arg1} {arg2}';

    /**
     * Run the program
     * @return Response
     */
    public function run(int $arg1, string $arg2)
    {
        print $arg1 .' '. $arg2;
    }
}
