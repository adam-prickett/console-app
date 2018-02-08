<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System;

use Exception;
use System\Console\Command;
use InvalidArgumentException;
use System\Console\CommandMap;
use System\Application\Bootstrap;
use System\Console\ConsoleOutput;
use System\Support\ArgumentParser;
use System\Support\ArgumentCollection;
use System\Support\ResolvesDependencies;

class Axo
{
    use ConsoleOutput,
        ResolvesDependencies;

    const VERSION = '2.4.0';

    /** @var string */
    protected $scriptName;

    /** @var array */
    protected $commandDirectories = [];

    /** @var CommandMap */
    protected $commandMap;

    /**
     * Initialize the Application
     */
    public function __construct()
    {
        Bootstrap::run();

        $this->commandMap = new CommandMap;
    }

    /**
     * Runs the Console Application
     *
     * @param  array    $arguments
     * @return output
     */
    public function run(array $arguments = null)
    {
        // Build a new CommandMap and enumerate the commands from the files within
        // the command directories specified in the $commandDirectories array
        $this->enumerateCommandsFromFilesystem();

        // If no arguments were passed to the run() method, grab the argv input from
        // the $_SERVER global and assign to the arguments for parsing and use
        if (empty($arguments)) {
            $arguments = $_SERVER['argv'];
        }

        $this->scriptName = array_shift($arguments);

        // Parse the arguments into a useful array
        $parsedArguments = (new ArgumentParser)
                            ->parse($arguments);

        // If no command is provided, print the command list
        if (empty($parsedArguments->getCommand())) {
            return $this->printCommandList();
        }

        // If this command exists in the map, run the command
        if ($this->commandMap->contains($parsedArguments->getCommand())) {
            return $this->runCommand($parsedArguments);
        }

        // Return the negative
        $this->error(sprintf('The command [%s] does not exist', $parsedArguments->getCommand()));
        return;
    }

    /**
     * Add a command directory to the Application
     *
     * @param   string $directory
     * @return  void
     */
    public function addCommandDirectory($directory, $namespace = null)
    {
        $this->commandDirectories[] = [
            'file'      => rtrim($directory, '/'),
            'namespace' => $namespace
        ];
    }

    /**
     * Add the System commands directory to the repository
     *
     * @return void
     */
    public function addSystemCommands()
    {
        $defaultCommandDirectories = [
            [
                'file'      => __DIR__.'/Commands',
                'namespace' => 'System\Commands'
            ],
        ];

        $this->commandDirectories = array_merge($defaultCommandDirectories, $this->commandDirectories);
    }

    /**
     * Return the version number
     *
     * @return float
     */
    public function version()
    {
        return self::VERSION;
    }

    /**
     * Run the command
     *
     * @param  ArgumentCollection   $arguments
     * @param  array                $map
     * @return
     */
    private function runCommand(ArgumentCollection $parameters)
    {
        // Extract the command from the CommandMap by it's name and initialise the
        // instance provided in the data array from the CommandMap storage
        $command = $this->commandMap->get($parameters->getCommand());
        $instance = $command['instance'];
        $instance->assignArguments($parameters);

        $options = $parameters->getOptions();

        // Handle --help option is provided
        if (isset($options['help'])) {
            return $this->handleHelpOption($instance, $parameters);
        }

        // Check for required() function and parse results
        if (! $this->handleRequired($instance, $parameters)) {
            return false;
        }

        // Silence any output if --quiet or -q are parsed
        if (isset($options['quiet']) or isset($options['q'])) {
            ob_start();
            return $this->call($instance, 'run', $parameters->getArguments());
            ob_end_clean();

            return;
        }

        return $this->call($instance, 'run', $parameters->getArguments());
    }

    /**
     * Prints the available commands when no command is provided
     *
     * @param  CommandMap $map
     * @return output
     */
    private function printCommandList()
    {
        // Get the commands for the listing
        $commands = array_keys($this->commandMap->all());

        // Now, let's get the length of the longest command to produce a prettified
        // table layout when printing the command list to the console all in line
        $commandColumWidth = $this->getLongestElementLength($commands) + 4;

        // Print the available commands
        $this->output(' ');
        $this->output('Available commands:');
        $this->output('--------------------');
        foreach ($this->commandMap->sort()->all() as $command => $data) {
            // Output this line to the console with the command name and description
            $this->warn(str_pad($command, $commandColumWidth), ['underscore'], false);
            $this->output($data['description']);
        }
        $this->output(' ');
    }

    /**
     * Check if --help option was provided and supply info
     *
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

        // Calculate the longest string length within the arguments and options to
        // allow us to display the data in a pretty column format in the console
        $argumentColumnWidth = $this->getLongestElementLength($instance->getArguments()) + 4;
        $optionColumnWidth = $this->getLongestElementLength($instance->getPossibleOptions()) + 6;

        $columnWidth = $argumentColumnWidth >= $optionColumnWidth ? $argumentColumnWidth : $optionColumnWidth;

        $this->output($instance->getDescription());

        $this->output(' ');

        $this->warn('USAGE');
        $this->output($this->formatCommand($instance));
        $this->output(' ');

        if (count($instance->getArguments()) > 0) {
            $this->warn('ARGUMENTS');

            foreach ($instance->getArguments() as $argument) {
                $this->info(str_pad($argument, $columnWidth), ['underscore'], false);
                $this->output($instance->getArgumentDescription($argument));
            }

            $this->output(' ');
        }

        $this->warn('OPTIONS');

        foreach ($instance->getPossibleOptions() as $option) {
            $this->info(str_pad('--'.$option, $columnWidth), ['underscore'], false);
            $this->output($instance->getOptionDescription($option));
        }

        // Show global --help option
        $this->info(str_pad('--help', $columnWidth), ['underscore'], false);
        $this->output('Displays this help text');

        // Show global --quiet option
        $this->info(str_pad('--quiet', $columnWidth), ['underscore'], false);
        $this->output('Silences the commands output');

        $this->output(' ');

        return 0;
    }

    /**
     * Check if required options are specified and parse
     *
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
            if (! $this->checkRequiredOption($option, $arguments)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check required params and validate
     *
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

        if (! is_array($option) and ! isset($options[$option])) {
            printf("%s option missing".PHP_EOL, $this->formatOption($option));
            return false;
        }

        return true;
    }

    /**
     * Format an option based on number of characters
     *
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
     *
     * @return string
     */
    private function formatCommand(Command $instance) : string
    {
        $required = $instance->getRequiredArguments();

        return sprintf('php %s %s %s', str_replace('./', '', $this->scriptName), $instance->getCommand(), implode(' ', array_map(function ($arg) use ($required) {
            return ! in_array($arg, $required) ? sprintf('[%s]', strtoupper($arg)) : strtoupper($arg);
        }, $instance->getArguments())));
    }

    /**
     * Enumerate commands from filesystem
     *
     * @return void
     */
    private function enumerateCommandsFromFilesystem()
    {
        $entries = [];

        // Generate the command directory listings
        foreach ($this->commandDirectories as $directoryEntry) {
            $entries[] = [
                'commands' => $this->scanCommandDirectory($directoryEntry['file']),
                'namespace' => $directoryEntry['namespace'] ?? null,
            ];
        }

        foreach ($entries as $entry) {
            foreach ($entry['commands'] as $file) {
                // Generate fully namespaced PSR-4 class name from filename
                $className = $this->createPsr4Namespace($file, $entry['namespace']);

                // Create an instance of the class
                $instance = $this->instance($className);

                if ($instance instanceof \System\Console\Command) {
                    if (! empty($instance->getCommand())) {
                        $this->commandMap->add($instance->getCommand(), [
                            'class' => $className,
                            'description' => $instance->getDescription(),
                            'instance' => $instance,
                        ]);
                    }
                }

                // Tidy-up
                unset($instance);
            }
        }
    }

    /**
     * Scan the Commands directory for .php files
     *
     * @return array
     */
    private function scanCommandDirectory($directory) : array
    {
        return array_filter(glob(sprintf('{%s/*.php}', $directory), GLOB_BRACE), function ($file) {
            return preg_match(env('COMMANDS_REGEX', '/\w\.php/'), $file);
        });
    }

    /**
     * Generate a PSR-4 compliant namespace string from an absolute file string
     *
     * @param  string $file
     * @param  string $namespace
     * @return string
     */
    private function createPsr4Namespace($file, $namespace = null) : string
    {
        $baseNamespace = str_replace(AXO_PATH, '', $file);
        $namespaceParts = explode('/', $baseNamespace);

        if (! empty($namespace)) {
            return sprintf('\\%s\%s', $namespace, ucfirst(str_replace('.php', '', basename($file))));
        }

        return '\\'.implode('\\', array_map(function ($value) {
            return ucfirst(str_replace('.php', '', $value));
        }, $namespaceParts));
    }

    /**
     * Get the integer value of the length of the longest value in the array
     *
     * @param  array  $array
     * @return int
     */
    protected function getLongestElementLength(array $array) : int
    {
        // Let's map the array we're given to return the string lengths of all the
        // elements it contains into a new array.
        $lengths = array_map(function ($val) {
            return strlen($val);
        }, $array);

        // Now we can sort the elements by their value in reverse order, putting the
        // longest by string length at the beginning of the resulting array
        rsort($lengths);

        // Now we can shift the first element off the array and return it giving us
        // the integer length of the longest element string in the array elements
        $longestCommand = array_shift($lengths);

        return $longestCommand ?: 5;
    }
}
