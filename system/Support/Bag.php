<?php

namespace System\Support;

class Bag
{
    /** @var array */
    protected $items = [];

    public function __construct($items = [])
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
     * Return the count of the items in the Bag
     * @return int
     */
    public function count() : int
    {
        return count($this->items);
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
     * Append a value to the Bag, without a key
     * @param  mixed $value
     * @return void
     */
    public function append($value)
    {
        $this->items[] = $value;
    }

    /**
     * Filter to unique records only
     * @return Bag
     */
    public function unique(string $key = null) : self
    {
        if (is_null($key)) {
            return new static(array_unique($this->items, SORT_REGULAR));
        }

        $unique = [];
        $exists = [];

        foreach ($this->items as $i => $item) {
            if (! in_array($this->value($item, $key), $exists)) {
                $unique[$i] = $item;
                $exists[] = $this->value($item, $key);
            }
        }

        return new static($unique);
    }

    /**
     * Sum the values of the items, or specific keys, and return the total
     * @param  string|null $key
     * @return int|float
     */
    public function sum(string $key = null)
    {
        if (is_null($key)) {
            return array_sum($this->items);
        }

        $sum = 0;
        foreach ($this->items as $i => $item) {
            $sum += $this->value($item, $key);
        }

        return $sum;
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
        return new static(array_filter($this->items, $callback));
    }

    /**
     * Iterate through the items reducing and carrying each time
     * @param  callable $callback
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
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

    /**
     * Automatically retrieve $key from $object in most scenarios
     *
     * @param  array|object $object
     * @param  mixed $key
     * @return mixed
     */
    private function value($object, $key)
    {
        if (is_array($object)) {
            return $object[$key];
        }

        if (is_object($object)) {
            return $object->{$key};
        }

        return false;
    }
}
