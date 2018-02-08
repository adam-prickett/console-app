<?php

namespace System\Support;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use System\Console\Command;
use InvalidArgumentException;
use System\Support\ArgumentCollection;
use System\Exceptions\DependencyResolutionException;

trait ResolvesDependencies
{
    /**
     * Get a list of depedencies for the specified method on the command
     *
     * @param  ReflectionClass|string  $command
     * @param  array                   $params
     * @return array
     */
    private function resolveDependencies($command, array $params = []) : array
    {
        $method = '__construct';

        if (is_array($command)) {
            $method = $command[1];
            $command = $command[0];
        }

        // Resolve the ReflectionClass object of any commands passed as namespaced
        // strings to the method. Following this, we need to check that we have
        // a valid ReflectionClass object before continuing without failing
        if (is_string($command)) {
            $command = new ReflectionClass($command);
        }

        if (! $command instanceof ReflectionClass) {
            throw new InvalidArgumentException('Invalid instance passed to resolveDependencies');
        }

        if (! $command->hasMethod($method)) {
            return [];
        }

        $classMethod = $command->getMethod($method);

        $parameters = $classMethod->getParameters();

        $resolved = [];

        foreach ($parameters as $position => $param) {
            try {
                if (isset($params[$param->getName()])) {
                    $resolved[] = $params[$param->getName()];
                    continue;
                }

                $resolved[] = ($param->getClass() === null) ? $this->resolveNativeDependency($param) : $this->resolveClassDependency($param);
            } catch (ReflectionException $e) {
                throw new DependencyResolutionException('Parameter at position '. ($position + 1) .' ['. $param->getType() .' $'. $param->getName() .'] could not be resolved');
            }
        }

        return $resolved;
    }

    /**
     * Resolve a native parameter dependency
     *
     * @param  ReflectionParameter  $parameter
     * @return mixed
     */
    private function resolveNativeDependency(ReflectionParameter $parameter)
    {
        if ($parameter->getDefaultValue() !== null) {
            return $parameter->getDefaultValue();
        }

        return null;
    }

    /**
     * Resolve a class based parameter dependency
     *
     * @param  ReflectionParameter  $parameter
     * @return mixed
     */
    private function resolveClassDependency(ReflectionParameter $parameter)
    {
        $className = $parameter->getClass()->name;

        return $this->instance($className);
    }

    /**
     * Return an instance of a class
     *
     * @param  mixed  $instance
     * @return mixed
     */
    private function instance($instance, array $params = [])
    {
        if (is_string($instance)) {
            $dependencies = [];

            $reflector = new ReflectionClass($instance);

            // If the instance we're tryng to instantiate has a constructor present,
            // then we'll automatically resolve the parameters for it and inject
            // them into the command so dependencies are met for the instance
            $constructor = $reflector->getConstructor();
            if ($constructor !== null) {
                $dependencies = $this->resolveDependencies([$reflector, $constructor->getName()], $params);
            }

            return new $instance(...$dependencies);
        }

        return false;
    }

    /**
     * Call a method on an instance and resolve dependencies
     *
     * @param  mixed  $instance
     * @param  string $method
     * @param  array  $params
     * @return mixed
     */
    private function call($instance, string $method, array $params = [])
    {
        if (is_string($instance)) {
            $instance = $this->instance($instance);
        }

        // Assign any passed arguments to the expected names from the command. This
        // allows us to automatically inject arguments into the given method on
        // the command by their name and therefore in their correct order
        $namedArguments = $this->assignNamedParameters($instance, $params);

        $dependencies = $this->resolveDependencies([get_class($instance), $method], $namedArguments);

        return call_user_func_array([$instance, $method], $dependencies);
    }

    /**
     * Creates a key/value array of the expected parameters and their values
     * from those provided in the console request.
     *
     * @param  System\Console\Command  $instance
     * @param  array                   $params
     * @return array
     */
    private function assignNamedParameters(Command $instance, array $params = []) : array
    {
        $namedParameters = [];

        foreach ($instance->getArguments() as $i => $parameterName) {
            if (isset($params[$i])) {
                $namedParameters[$parameterName] = $params[$i];
            }
        }

        return $namedParameters;
    }
}
