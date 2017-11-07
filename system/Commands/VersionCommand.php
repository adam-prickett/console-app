<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System\Commands;

use System\Axo;
use System\Log\Log;
use System\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'version (Print the current running version of Axo)';
    
    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        $this->output((new Axo)->version());
    }
}
