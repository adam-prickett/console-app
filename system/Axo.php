<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System;

use System\Console\Command;
use System\Application\Bootstrap;
use System\Console\ConsoleOutput;
use System\Support\ArgumentParser;
use System\Support\ArgumentCollection;

class Axo
{
    use ConsoleOutput;

    const VERSION = '2.0.3';

    protected $scriptName;

    protected $commandDirectories = [];

    /**
     * Runs the Console Application
     * @param  array    $arguments
     * @return output
     */
    public function run(array $arguments = null)
    {
        Bootstrap::run();
        
        $defaultCommandDirectories = [
            [
                'file'      => __DIR__.'/Commands',
                'namespace' => 'System\Commands'
            ],
        ];

        $this->commandDirectories = array_merge($defaultCommandDirectories, $this->commandDirectories);

        $commandMap = $this->enumerateCommandsFromFiles();

        if (empty($arguments)) {
            $arguments = $_SERVER['argv'];
        }
        $this->scriptName = array_shift($arguments);

        // Parse the arguments into a useful array
        $parser = new ArgumentParser;
        $parsed = $parser->parse($arguments);

        // If no command is provided, print the command list
        if (empty($parsed->getCommand())) {
            return $this->printCommands($commandMap);
        }

        // If this command exists in the map, run the command
        if (isset($commandMap[$parsed->getCommand()])) {
            return $this->runCommand($parsed, $commandMap);
        }

        // Return the negative
        printf('%s does not exist'.PHP_EOL, $parsed->getCommand());
        return;
    }

    /**
     * Add a command directory to the Application
     * @param string $directory
     */
    public function addCommandDirectory($directory, $namespace = null)
    {
        $this->commandDirectories[] = [
            'file'      => rtrim($directory, '/'),
            'namespace' => $namespace
        ];
    }

    /**
     * Return the version number
     * @return float
     */
    public function version()
    {
        return self::VERSION;
    }

    /**
     * Run the command
     * @param  ArgumentCollection   $arguments
     * @param  array                $map
     * @return
     */
    private function runCommand(ArgumentCollection $arguments, array $map)
    {
        $className = $map[$arguments->getCommand()];
        $instance = new $className($arguments);

        $options = $arguments->getOptions();

        // Handle --help option is provided
        if (isset($options['help'])) {
            return $this->handleHelpOption($instance, $arguments);
        }

        // Check for required() function and parse results
        if (!$this->handleRequired($instance, $arguments)) {
            return false;
        }

        // Silence any output if --quiet or -q are parsed
        if (isset($options['quiet']) or isset($options['q'])) {
            ob_start();
            $instance->run();
            ob_end_clean();

            return;
        }

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
        $this->output('--------------------');
        $this->warn('Available commands:', ['underscore', 'bold']);
        $this->output('--------------------');
        foreach ($commands as $command) {
            $className = $map[$command];
            $instance = new $className();
            
            $this->output($command, ['underscore'], false);
            $this->output(' - '.$instance->getDescription());

            unset($instance);
            unset($className);
        }
        $this->output('--------------------');
    }

    /**
     * Check if --help option was provided and supply info
     * @param  Command              $instance
     * @param  ArgumentCollection   $arguments
     * @return mixed
     */
    private function handleHelpOption(Command $instance, ArgumentCollection $arguments)
    {
        if (method_exists($instance, 'help')) {
            print $instance->help().PHP_EOL.PHP_EOL;
            return;
        }

        printf('%s does not provide any help'.PHP_EOL, $arguments->getCommand());
        return 0;
    }

    /**
     * Check if required options are specified and parse
     * @param  Command              $instance
     * @param  ArgumentCollection   $arguments
     * @return mixed
     */
    private function handleRequired(Command $instance, ArgumentCollection $arguments)
    {
        // Check for number of required arguments
        if (count($instance->getRequiredArguments()) > count($arguments->getArguments())) {
            print $this->formatCommand($instance).PHP_EOL;
            return false;
        }

        // Iterate over required options, if provided
        foreach ($instance->getRequiredOptions() as $option) {
            if (!$this->checkRequiredOption($option, $arguments)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check required params and validate
     * @param  string|array         $option
     * @param  ArgumentCollection   $arguments
     * @return void
     */
    private function checkRequiredOption($option, ArgumentCollection $arguments)
    {
        $options = $arguments->getOptions();

        // $option can be an array for variations
        if (is_array($option)) {
            $present = array_filter($option, function ($value) use ($options) {
                return isset($options[$value]);
            });

            if (count($present) == 0) {
                printf("%s option missing".PHP_EOL, implode(' / ', array_map(function ($opt) {
                    return $this->formatOption($opt);
                }, $option)));
                return false;
            }
        }
        
        if (!is_array($option) and !isset($options[$option])) {
            printf("%s option missing".PHP_EOL, $this->formatOption($option));
            return false;
        }

        return true;
    }

    /**
     * Format an option based on number of characters
     * @param  string $option
     * @return string
     */
    private function formatOption($option)
    {
        if (substr($option, 0, 1) == '-') {
            return $option;
        }

        return strlen($option) == 1 ? '-'.$option : '--'.$option;
    }

    /**
     * Format the command syntax for outputting
     * @return string
     */
    private function formatCommand(Command $instance)
    {
        $required = $instance->getRequiredArguments();
        return sprintf("php %s %s %s", str_replace('./', '', $this->scriptName), $instance->getCommand(), implode(' ', array_map(function ($arg) use ($required) {
            return !in_array($arg, $required) ? sprintf('[%s]', strtoupper($arg)) : strtoupper($arg);
        }, $instance->getArguments())));
    }

    /**
     * Enumerate commands from filesystem
     * @return array
     */
    private function enumerateCommandsFromFiles()
    {
        $commands = [];
        $entries = [];

        // Generate the command directory listings
        foreach ($this->commandDirectories as $directoryEntry) {
            $entries[] = ['commands' => $this->scanCommandDirectory($directoryEntry['file']), 'namespace' => $directoryEntry['namespace'] ?? null];
        }

        foreach ($entries as $entry) {
            foreach ($entry['commands'] as $file) {
                // Generate fully namespaced PSR-4 class name from filename
                $className = $this->createPsr4Namespace($file, $entry['namespace']);

                // Create an instance of the class
                $instance = new $className();
                
                if ($instance instanceof \System\Console\Command) {
                    if (!empty($instance->getCommand())) {
                        $commands[$instance->getCommand()] = $className;
                    }
                }

                // Tidy-up
                unset($instance);
            }
        }

        return $commands;
    }

    /**
     * Scan the Commands directory for .php files
     * @return array
     */
    private function scanCommandDirectory($directory)
    {
        return array_filter(glob(sprintf('{%s/*.php}', $directory), GLOB_BRACE), function ($file) {
            return preg_match(env('COMMANDS_REGEX', '/\w\.php/'), $file);
        });
    }

    /**
     * Generate a PSR-4 compliant namespace string from an absolute file string
     * @param  string $file
     * @param  string $namespace
     * @return string
     */
    private function createPsr4Namespace($file, $namespace = null)
    {
        $baseNamespace = str_replace(AXO_PATH, '', $file);
        $namespaceParts = explode('/', $baseNamespace);

        if (!empty($namespace)) {
            return sprintf('\\%s\%s', $namespace, ucfirst(str_replace('.php', '', basename($file))));
        }
        
        return '\\'.implode('\\', array_map(function ($value) {
            return ucfirst(str_replace('.php', '', $value));
        }, $namespaceParts));
    }
}
