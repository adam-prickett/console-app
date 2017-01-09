<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@gmail.com>
 * @license MIT
 * @copyright © Copyright Adam Prickett 2017.
 */

namespace System\Console;

use System\Console\CommandOutput;
use System\Console\CommandProgressBar;
use System\Support\ArgumentCollection;

class Command
{
    use CommandOutput, CommandProgressBar;

    /** @var string The command to run this Command */
    protected $command;
        
    /** @var string */
    protected $description;

    /** @var array */
    protected $possibleOptions = [];

    /** @var array */
    protected $requiredOptions = [];

    /** @var array */
    protected $possibleArguments = [];

    /** @var array */
    protected $requiredArguments = [];

    /** @var array Arguments passed to the command */
    protected $arguments = [];

    /** @var array Options passed to the command */
    protected $options = [];

    public function __construct(ArgumentCollection $arguments = null)
    {
        if ($arguments instanceof ArgumentCollection) {
            $this->options = $arguments->getOptions();
            $this->arguments = $arguments->getArguments();
        }

        // Setup the Command if the method exists
        if (method_exists($this, 'setup')) {
            $this->setup();
        }
    }

    /**
     * Return an option passed by the command line
     * @param  string|array   $value
     * @param  mixed|boolean  $default
     * @return mixed|boolean
     */
    public function option($value, $default = false)
    {
        // Handle multiple values
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($this->option($v) !== false) {
                    return $this->option($v);
                }
            }
        }
        
        if (!is_array($value) and isset($this->options[$value])) {
            return $this->options[$value];
        }

        return $default;
    }

    /**
     * Return an argument passed by the command line
     * @param  int            $num
     * @param  mixed|boolean  $default
     * @return mixed|boolean
     */
    public function argument($key, $default = false)
    {
        $value = null;
        foreach ($this->getArguments() as $num => $value) {
            if (strtolower($key) == strtolower($value)) {
                return $this->arguments[$num];
            }
        }

        return $default;
    }

    /**
     * Set the command used to run app
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get the command for this Command
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add a required option to the Command
     * @param string|array $option
     */
    public function requiresOption($option)
    {
        $this->requiredOptions[] = $option;
        
        return $this;
    }

    /**
     * Requires an argument for the Command
     * @param string $argument
     */
    public function requiresArgument($argument)
    {
        $this->possibleArguments[] = $argument;
        $this->requiredArguments[] = $argument;

        return $this;
    }

    /**
     * Accepts an argument for the Command
     * @param  string $argument
     */
    public function acceptsArgument($argument)
    {
        $this->possibleArguments[] = $argument;

        return $this;
    }

    /**
     * Returns the required options for this Command
     * @return array
     */
    public function getRequiredOptions()
    {
        return $this->requiredOptions;
    }

    /**
     * Returns the acceptable arguments to this Command
     * @return array
     */
    public function getArguments()
    {
        return $this->possibleArguments;
    }

    /**
     * Returns the required arguments to this Command
     * @return array
     */
    public function getRequiredArguments()
    {
        return $this->requiredArguments;
    }
}
