<?php

namespace System\Support;

class ArgumentParser
{
    /** @var string The command name parsed from the arguments */
    protected $command;

    /** @var array Parsed options from the arguments */
    protected $options = [];

    /** @var array Parsed arguments from the arguments */
    protected $args = [];
    
    /** @var boolean Holds whether arguments have yet been processed */
    protected $reachedArguments = false;
    

    /**
     * Parse provided arguments and return associative array
     * @param  array|null $arguments Array of arguments, or null to use $argv
     * @return array
     */
    public function parse(array $arguments = null)
    {
        // If $arguments is null, grab the argv input from $_SERVER
        if (is_null($arguments)) {
            $arguments = $_SERVER['argv'];
            array_shift($arguments);
        }

        // Get the command from the beginning of the args
        $this->command = array_shift($arguments);

        // Loop each argument provided
        foreach ($arguments as $arg) {
            $this->parseArgument($arg);
        }

        return $this->compileArray();
    }

    /**
     * Parse a single argument, determines if it is an option or argument
     * and saves.
     * @param  string $argument
     * @return void
     */
    protected function parseArgument($argument)
    {
        if ($this->isOption($argument) or $this->isShortOption($argument)) {
            // Options may only be passed before any arguments
            if ($this->reachedArguments) {
                throw new \Exception('Options must be passed before arguments');
            }

            $option = $this->isValueOption($argument) ? $this->parseValueOption($argument) : $this->parseOption($argument, $this->isShortOption($argument));
            $this->addOption($option['key'], $option['value']);
        }

        if ($this->isArgument($argument)) {
            $this->reachedArguments = true;
            $this->addArgument($argument);
        }
    }

    /**
     * Determines if the supplied argument is an option
     * @param  string  $argument
     * @return boolean
     */
    protected function isOption($argument)
    {
        return substr($argument, 0, 2) == '--';
    }

    /**
     * Determines if the supplied argument is a shorthand option
     * @param  string  $argument
     * @return boolean
     */
    protected function isShortOption($argument)
    {
        return substr($argument, 0, 1) == '-' and substr($argument, 1, 1) != '-';
    }

    /**
     * Determines if the supplied argument is an argument
     * @param  string  $argument
     * @return boolean
     */
    protected function isArgument($argument)
    {
        return substr($argument, 0, 1) != '-';
    }

    /**
     * Determines if the supplied argument is a value option
     * @param  string  $argument
     * @return boolean
     */
    protected function isValueOption($argument)
    {
        return strpos($argument, '=') !== false;
    }

    /**
     * Parses an option string into key => true
     * @param  string  $argument
     * @return array
     */
    protected function parseOption($argument, $short = false)
    {
        if ($short) {
            return $this->parseShortOption($argument);
        }

        $key = trim(substr($argument, !$short ? 2 : 1));

        return [
            'key' => $key,
            'value' => true,
        ];
    }

    /**
     * Parse a short (-t) format option (with optional value)
     * @param  string $argument
     * @return array
     */
    protected function parseShortOption($argument)
    {
        $argument = ltrim($argument, '-');
        $value = true;
        
        // If there's more than 1 character, this must be a closed value option
        if (strlen($argument) >= 2) {
            $value = trim(substr($argument, 1), '"');
            $argument = substr($argument, 0, 1);
        }

        return [
            'key' => $argument,
            'value' => $value,
        ];
    }

    /**
     * Parses a value option string into key => value
     * @param  string  $argument
     * @return array
     */
    protected function parseValueOption($argument)
    {
        list($key, $value) = explode('=', $argument, 2);
        $value = trim($value, '"');
        $key = ltrim($key, '-');

        return [
            'key' => $key,
            'value' => $value,
        ];
    }

    /**
     * Adds an option to the return values
     * @param string $key
     * @param string $value
     */
    protected function addOption($key, $value = null)
    {
        $this->options[$key] = $value;
    }

    /**
     * Adds a short option to the return values
     * @param string $key
     * @param string $value
     */
    protected function addShortOption($key, $value = null)
    {
        // At this stage there's no difference between an option and short option.
        $this->options[$key] = $value;
    }

    /**
     * Adds an argument to the return values
     * @param string $argument
     */
    protected function addArgument($argument)
    {
        $this->args[] = $argument;
    }

    /**
     * Compiles the return array
     * @return array
     */
    protected function compileArray()
    {
        return [
            'command' => $this->command,
            'options' => $this->options,
            'args' => $this->args
        ];
    }
}
