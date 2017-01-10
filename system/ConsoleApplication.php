<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@gmail.com>
 * @license MIT
 * @copyright © Copyright Adam Prickett 2017.
 */

namespace System;

use App\ConsoleKernel;
use System\Console\Command;
use System\Console\ConsoleOutput;
use System\Support\ArgumentParser;
use System\Support\ArgumentCollection;

class ConsoleApplication
{
    use ConsoleOutput;

    protected $scriptName;

    /**
     * Runs the Console Application
     * @param  array    $arguments
     * @return output
     */
    public function run()
    {
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
        die;
    }

    /**
     * Run the command
     * @param  ArgumentCollection   $arguments
     * @param  array                $map
     * @return
     */
    private function runCommand(ArgumentCollection $arguments, array $map)
    {
        $class = new \ReflectionClass($map[$arguments->getCommand()]);
        $instance = $class->newInstanceArgs([$arguments]);

        $options = $arguments->getOptions();

        // Handle --help option is provided
        if (isset($options['help'])) {
            return $this->handleHelpOption($instance, $arguments);
        }

        // Check for required() function and parse results
        $this->handleRequired($instance, $arguments);

        // Silence any output if --quiet or -q are parsed
        if (isset($options['quiet']) or isset($options['q'])) {
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
        $this->output('---------------');
        $this->warn('Available commands:', ['underscore', 'bold']);
        $this->output('---------------');
        foreach ($commands as $command) {
            $class = new \ReflectionClass($map[$command]);
            $instance = $class->newInstanceArgs();
            
            $this->output($command, ['underscore'], false);
            $this->output(' - '.$instance->getDescription());

            unset($instance);
            unset($class);
        }
        $this->output('---------------');
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
            print $instance->help();
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
            print $this->formatCommand($instance);
            die;
        }

        // Iterate over required options, if provided
        foreach ($instance->getRequiredOptions() as $option) {
            $this->checkRequiredOption($option, $arguments);
        }
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
                printf("%s option missing", implode(' / ', array_map(function ($opt) {
                    return $this->formatOption($opt);
                }, $option)));
                die;
            }
        }
        
        if (!is_array($option) and !isset($options[$option])) {
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

    /**
     * Format the command syntax for outputting
     * @return string
     */
    private function formatCommand(Command $instance)
    {
        $required = $instance->getRequiredArguments();
        return sprintf("php %s %s %s", $this->scriptName, $instance->getCommand(), implode(' ', array_map(function ($arg) use ($required) {
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
        $files = $this->scanCommandDirectory();
        foreach ($files as $file) {
            // Generate fully namespaced PSR-4 class name from filename
            $className = $this->createPsr4Namespace($file);

            // Create an instance of the class
            $class = new \ReflectionClass($className);
            $instance = $class->newInstanceArgs();
            
            if ($instance instanceof \System\Console\Command) {
                if (!empty($instance->getCommand())) {
                    $commands[$instance->getCommand()] = $className;
                }
            }
        }

        return $commands;
    }

    /**
     * Scan the Commands directory for .php files
     * @return array
     */
    private function scanCommandDirectory()
    {
        return array_filter(glob("{".FRONT_CONTROLLER_PATH.env('COMMANDS_DIR', 'commands')."/*.php,".FRONT_CONTROLLER_PATH."system/Commands/*.php}", GLOB_BRACE), function ($file) {
            return preg_match(env('COMMANDS_REGEX', '/\w\.php/'), $file);
        });
    }

    /**
     * Generate a PSR-4 compliant namespace string from an absolute file string
     * @param  string $file
     * @return string
     */
    private function createPsr4Namespace($file)
    {
        $baseNamespace = str_replace(FRONT_CONTROLLER_PATH, '', $file);
        $namespaceParts = explode('/', $baseNamespace);

        return '\\'.implode('\\', array_map(function ($value) {
            return ucfirst(str_replace('.php', '', $value));
        }, $namespaceParts));
    }
}
