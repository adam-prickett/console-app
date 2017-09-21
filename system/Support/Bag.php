<?php

namespace System\Support;

class Bag
{
    /** @var array */
    protected $items = [];

    public function __construct($items)
    {
        $this->items = $this->mutateToArray($items);
    }

    /**
     * Return all items from the Bag
     * @return array
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * Determine if this Bag contains a value
     * @param  string $value
     * @return bool
     */
    public function contains(string $value) : bool
    {
        return in_array($value, $this->items);
    }

    /**
     * Determine if this Bag contains a key
     * @param  string  $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get a value from the Bag by key
     * @param  string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (! $this->has($key)) {
            return false;
        }

        return $this->items[$key];
    }

    /**
     * Set a value to the Bag with a key
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value)
    {
        $this->items[$key] = $value;
    }

    /**
     * Filter to unique records only
     * @return Bag
     */
    public function unique() : self
    {
        return new static(array_unique($this->items));
    }

    /**
     * Merge items into this Bag
     * @param  array|Bag $with
     * @return Bag
     */
    public function merge($with) : self
    {
        return new static(array_merge($this->all(), $this->mutateToArray($with)));
    }

    /**
     * Map the items with $callback and return instance
     * @param  callable $callback
     * @return Bag
     */
    public function map(callable $callback) : self
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Filter the items with $callback and return instance
     * @param  callable $callback
     * @return Bag
     */
    public function filter(callable $callback) : self
    {
        return new static(array_filter($this->itms, $callback));
    }

    /**
     * Return the Collection contents as an Array
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->all();
    }

    /**
     * Return the Collection contents as an JSON string
     *
     * @return string
     */
    public function toJson() : string
    {
        return json_encode($this->all());
    }

    /**
     * Return the Collection contents as a list
     *
     * @return string
     */
    public function toList() : string
    {
        return implode("\n", $this->all());
    }

    /**
     * Magic getter for values
     * @param  string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Magic setter for values
     * @param string $key
     * @param mixed  $value
     */
    public function __set(string $key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Magic isset() for values
     * @param  string  $key
     * @return bool
     */
    public function __isset(string $key) : bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Mutate a variable to an array
     * @param  mixed $items
     * @return array
     */
    private function mutateToArray($items) : array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->toArray();
        }

        if (is_string($items)) {
            return [$items];
        }

        if (is_object($items) and method_exists($items, 'toArray')) {
            return $items->toArray();
        }
        
        return [];
    }
}
