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

class InjectionCommand extends Command
{
    protected $signature = 'inject';

    public function __construct(Bag $bag)
    {
        parent::__construct();

        $this->bag = $bag;
    }

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        if ($this->bag instanceof \System\Support\Bag) {
            print 'Bag is initialised';
        }
    }
}
