<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@gmail.com>
 * @license MIT
 * @copyright © Copyright Adam Prickett 2016.
 */

namespace App;

class ConsoleKernel
{
    /**
     * Maps the commands to the relevant class
     * @return array
     */
    public function mapCommands()
    {
        return [
            'example' => \App\Commands\ExampleCommand::class,
        ];
    }
}
