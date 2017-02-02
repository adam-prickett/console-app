<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System\Support;

class ArgumentCollection
{
    /** @var string */
    protected $command;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $arguments = [];

    public function __construct($items = null)
    {
        if (is_array($items)) {
            if (isset($items['options'])) {
                $this->options = $items['options'];
            }

            if (isset($items['args'])) {
                $this->arguments = $items['args'];
            }

            if (isset($items['command'])) {
                $this->command = $items['command'];
            }
        }
    }

    /**
     * Set the Command attribute
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get the Command stored in the Collection
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set the Options attribute
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Return the Options stored in the Collections
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the Arguments attribute
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Return the Arguments from the Collection
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
