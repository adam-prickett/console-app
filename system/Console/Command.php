<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@gmail.com>
 * @license MIT
 * @copyright © Copyright Adam Prickett 2016.
 */

namespace System\Console;

class Command
{
    /** @var array Arguments passed to the command */
    protected $arguments = [];

    /** @var array Options passed to the command */
    protected $options = [];

    public function __construct(array $args = null)
    {
        if (!is_null($args)) {
            if (isset($args['options'])) {
                $this->options = $args['options'];
            }

            if (isset($args['args'])) {
                $this->arguments = $args['args'];
            }
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
    public function argument($num, $default = false)
    {
        // Allow us to start on 1, for ease
        $num = $num-1;

        if (isset($this->arguments[$num])) {
            return $this->arguments[$num];
        }

        return $default;
    }
}
