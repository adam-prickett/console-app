<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System\Console;

use System\Console\ConsoleOutput;
use System\Console\ConsoleProgressBar;
use System\Support\ArgumentCollection;

class Command
{
    use ConsoleOutput, ConsoleProgressBar;

    /** @var string The command to run this Command */
    protected $command;

    /** @var string The command signature to set the command and options */
    protected $signature;
        
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

    /** @var array Array of descriptions for options */
    protected $optionDescriptions = [];

    /** @var array Array of default values for options */
    protected $optionDefaults = [];

    /** @var array Array of descriptions for arguments */
    protected $argumentDescriptions = [];

    /** @var array Array of default values for arguments */
    protected $argumentDefaults = [];

    public function __construct(ArgumentCollection $arguments = null)
    {
        if ($arguments instanceof ArgumentCollection) {
            $this->options = $arguments->getOptions();
            $this->arguments = $arguments->getArguments();
        }

        // Setup the Command if the method exists
        // Kept for backwards compatibility
        if (method_exists($this, 'setup')) {
            $this->setup();
        }

        $this->parseSignature();
    }

    /**
     * Assign an ArgumentCollection to the Command
     *
     * @param  ArgumentCollection $arguments
     * @return Command
     */
    public function assignArguments(ArgumentCollection $arguments) : self
    {
        if ($arguments instanceof ArgumentCollection) {
            $this->options = $arguments->getOptions();
            $this->arguments = $arguments->getArguments();
        }

        return $this;
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
        
        if (! is_array($value) and isset($this->options[$value])) {
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

    /**
     * Set the description for the command
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Retrieve the description of the command
     * @return string
     */
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
        return $this->acceptsOption($option, true);
    }

    /**
     * Accepts an option to the Command
     * @param string|array $option
     * @param bool         $required
     */
    public function acceptsOption($option, $required = false, string $description = null, $default = null)
    {
        $this->possibleOptions[] = $option;
        if ($required) {
            $this->requiredOptions[] = $option;
        }

        $this->optionDescriptions[$this->getMainOptionParameter($option)] = $description;
        $this->optionDefaults[$this->getMainOptionParameter($option)] = $default;
        
        return $this;
    }

    /**
     * Requires an argument for the Command
     * @param string $argument
     */
    public function requiresArgument($argument)
    {
        return $this->acceptsArgument($argument, true);
    }

    /**
     * Accepts an argument for the Command
     * @param  string $argument
     */
    public function acceptsArgument($argument, $required = false, string $description = null, $default = null)
    {
        $this->possibleArguments[] = $argument;
        if ($required) {
            $this->requiredArguments[] = $argument;
        }

        $this->argumentDescriptions[$argument] = $description;
        $this->argumentDefaults[$argument] = $default;

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
     * Returns the possible options for this Command
     * @return array
     */
    public function getPossibleOptions()
    {
        return $this->possibleOptions;
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
     * Return the argument descriptions array
     *
     * @return array
     */
    public function getArgumentDescriptions() : array
    {
        return $this->argumentDescriptions;
    }

    /**
     * Return the description for a given argument
     *
     * @return string
     */
    public function getArgumentDescription(string $argument) : string
    {
        return $this->argumentDescriptions[$argument] ?? null;
    }

    /**
     * Return the option descriptions array
     *
     * @return array
     */
    public function getOptionDescriptions() : array
    {
        return $this->optionDescriptions;
    }

    /**
     * Return the description for a given option
     *
     * @return string
     */
    public function getOptionDescription(string $option) : string
    {
        return $this->optionDescriptions[$option] ?? null;
    }

    /**
     * Returns the required arguments to this Command
     * @return array
     */
    public function getRequiredArguments()
    {
        return $this->requiredArguments;
    }

    /**
     * Parse the command signature to grab the arguments and options
     * @return void
     */
    protected function parseSignature()
    {
        // Capture the command from the signature
        preg_match('/[^\s]+/', $this->signature, $signatureParts);
        if (empty($signatureParts[0])) {
            throw new InvalidArgumentException('Could not determine command from signature');
        }

        $this->setCommand($signatureParts[0]);

        // Capture any description provided in parenthesis.
        if (preg_match('/\(([^\)]+)\)/', $this->signature, $matches, PREG_OFFSET_CAPTURE)) {
            $offsetEnd = $matches[0][1]+strlen($matches[0][0]);
            $this->setDescription($matches[1][0]);
        }

        // Locate parameters for processing
        preg_match_all('/\{([^\}]+)\}/', $this->signature, $parameters);

        // Iterate over the found options and apply
        if (! empty($parameters[1]) and is_array($parameters[1])) {
            foreach ($parameters[1] as $parameter) {
                $this->parseSignatureParameter($parameter);
            }
        }
    }

    /**
     * Parses a signature parameter and actions
     * @param  string $parameter
     * @return void
     */
    protected function parseSignatureParameter($parameter)
    {
        $description = null;
        $default = null;

        // Assume parameter is required unless ? flag is provided
        $required = true;
        if (substr($parameter, -1) == '?') {
            $required = false;
            $parameter = substr($parameter, 0, -1);
        }

        // Check for a colon (:) character in the parameter. If we find one, split
        // on this as anything following this is a description of the parameter
        // for the help display function
        if (stripos($parameter, ':') !== false) {
            list($parameter, $description) = array_map('trim', explode(':', $parameter));
        }

        // Check for multiple options/shortcuts
        if (stripos($parameter, '|')) {
            $parameterParts = array_map('trim', explode('|', $parameter));
            // Only works with options, so check we only have options or short options here.
            foreach ($parameterParts as $part) {
                if (substr($part, 0, 1) != '-') {
                    throw new InvalidArgumentException('Only options may be aliased in signature');
                }
            }

            return $this->acceptsOption($this->normaliseOptionParameter($parameterParts), $required, $description, $default);
        }

        // Check for options
        if (substr($parameter, 0, 1) == '-') {
            return $this->acceptsOption($this->normaliseOptionParameter($parameter), $required, $description, $default);
        }

        // Must be an argument this far down
        return $this->acceptsArgument($parameter, $required, $description, $default);
    }

    /**
     * Retrieve the main option parameter
     *
     * @param  string|array $parameter
     * @return string
     */
    protected function getMainOptionParameter($parameter) : string
    {
        if (is_array($parameter)) {
            return $this->normaliseOptionParameter(array_shift($parameter));
        }

        return $this->normaliseOptionParameter($parameter);
    }

    /**
     * Normalise parameters by removing prefixes, lowercasing and trimming
     * @param  string|array $parameter
     * @return string|array
     */
    protected function normaliseOptionParameter($parameter)
    {
        if (is_array($parameter)) {
            return array_map(function ($value) {
                return $this->normaliseOptionParameter($value);
            }, $parameter);
        }

        return mb_strtolower(trim(ltrim($parameter, '-')));
    }
}
