<?php

namespace System\Console;

use InvalidArgumentException;

class CommandMap
{
    protected $map = [];

    /**
     * Initialise the command map
     *
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $command => $data) {
            $this->addCommand($command, $data);
        }
    }

    /**
     * Add a command to the command map
     *
     * @param  string $command
     * @param  array  $data
     * @return CommandMap
     */
    public function add(string $command, array $data) : self
    {
        $this->map[$command] = $data;

        return $this;
    }

    /**
     * Return a named command from the command map
     *
     * @param  string $command
     * @return array
     */
    public function get(string $command) : array
    {
        if (! isset($this->map[$command])) {
            throw new InvalidArgumentException(sprintf('The command %s does not exist in the Command Map', $command));
        }

        return $this->map[$command];
    }

    /**
     * Return an array containing all the commands in the map
     *
     * @return array
     */
    public function all() : array
    {
        return $this->map;
    }

    /**
     * Determine whether this map contains a given command
     *
     * @param  string $command
     * @return bool
     */
    public function contains(string $command) : bool
    {
        return isset($this->map[$command]);
    }

    /**
     * Sort the command map by the command names
     *
     * @param  bool   $reverse
     * @return CommandMap
     */
    public function sort(bool $reverse = false) : self
    {
        if ($reverse) {
            krsort($this->map);
            return $this;
        }

        ksort($this->map);
        return $this;
    }
}
