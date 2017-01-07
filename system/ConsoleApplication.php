<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@gmail.com>
 * @license MIT
 * @copyright © Copyright Adam Prickett 2016.
 */

namespace System;

use App\ConsoleKernel;
use System\Support\ArgumentParser;

class ConsoleApplication
{
    /**
     * Runs the Console Application
     * @param  array $arguments
     * @return output
     */
    public function run(array $arguments = null)
    {
        // Load the Kernel
        $kernel = new ConsoleKernel;
        $map = $kernel->mapCommands();
        
        // Parse the arguments into a useful array
        $parser = new ArgumentParser;
        $parsed = $parser->parse($arguments);

        // If no command is provided, print the command list
        if (empty($parsed['command'])) {
            return $this->printCommands($map);
        }

        // If this command exists in the map, run the command
        if (isset($map[$parsed['command']])) {
            return $this->runCommand($parsed, $map);
        }

        // Return the negative
        printf('%s does not exist'.PHP_EOL, $parsed['command']);
        die;
    }

    /**
     * Run the command
     * @param  array  $arguments
     * @param  array  $map
     * @return
     */
    private function runCommand(array $arguments, array $map)
    {
        $class = new \ReflectionClass($map[$arguments['command']]);
        $instance = $class->newInstanceArgs([$arguments]);

        // Handle --help option is provided
        if (isset($arguments['options']['help'])) {
            return $this->handleHelpOption($instance, $arguments);
        }

        // Check for required() function and parse results
        $this->handleRequired($instance, $arguments);

        // Silence any output if --quiet or -q are parsed
        if (isset($arguments['options']['quiet']) or isset($arguments['options']['q'])) {
            ob_start();
            $instance->run();
            ob_end_clean();

            return;
        }

        // Run the Command program "normally"
        return $instance->run();
    }

    /**
     * Prints the available commands when no command is provided
     * @return output
     */
    private function printCommands(array $map)
    {
        // Get the commands for the listing
        $commands = array_keys($map);
        sort($commands);

        // Print the available commands
        print '---------------'.PHP_EOL;
        print 'Available commands:'.PHP_EOL;
        print '---------------'.PHP_EOL;
        foreach ($commands as $command) {
            $class = new \ReflectionClass($map[$command]);
            $instance = $class->newInstanceArgs();
            
            // If the Command exposes description(), print alongside.
            if (method_exists($instance, 'description')) {
                print $command.' - '.$instance->description().PHP_EOL;
            } else {
                print $command.PHP_EOL;
            }

            unset($instance);
            unset($class);
        }
        print '---------------'.PHP_EOL;
    }

    /**
     * Check if --help option was provided and supply info
     * @param  ReflectionClass $instance
     * @param  array           $opts
     * @return mixed
     */
    private function handleHelpOption($instance, $args)
    {
        if (method_exists($instance, 'help')) {
            print $instance->help();
            return;
        }

        printf('%s does not provide any help'.PHP_EOL, $args['command']);
        return 0;
    }

    /**
     * Check if required options are specified and parse
     * @param  ReflectionClass  $instance
     * @param  array            $args
     * @return mixed
     */
    private function handleRequired($instance, $args)
    {
        if (method_exists($instance, 'required')) {
            $required = $instance->required();
            
            // Iterate over required options, if provided
            if (isset($required['options'])) {
                foreach ($required['options'] as $option) {
                    $this->checkRequiredOption($option, $args);
                }
            }

            // Check for number of required arguments
            if (isset($required['arguments']) and count($args['args']) != $required['arguments']) {
                printf("%d arguments required", $required['arguments']);
                die;
            }
        }
    }

    /**
     * Check required params and validate
     * @param  string|array $option
     * @param  array        $args
     * @return void
     */
    private function checkRequiredOption($option, $args)
    {
        // $option can be an array for variations
        if (is_array($option)) {
            $present = array_filter($option, function ($value) use ($args) {
                return isset($args['options'][$value]);
            });

            if (count($present) == 0) {
                printf("%s option missing", implode(' / ', array_map(function ($opt) {
                    return $this->formatOption($opt);
                }, $option)));
                die;
            }
        }
            
        if (!is_array($option) and !isset($args['options'][$option])) {
            printf("%s option missing", $this->formatOption($option));
            die;
        }
    }

    /**
     * Format an option based on number of characters
     * @param  string $option
     * @return string
     */
    private function formatOption($option)
    {
        return strlen($option) == 1 ? '-'.$option : '--'.$option;
    }
}
