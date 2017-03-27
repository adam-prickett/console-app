<?php

namespace System\Log;

use Monolog\Logger;
use BadMethodCallException;
use Monolog\Handler\StreamHandler;

class Log
{
    /** @var Logger Monolog instance */
    protected static $logger;

    /** @var array Array of log level aliases */
    protected static $logLevels = [
        'debug'     => Logger::DEBUG,
        'info'      => Logger::INFO,
        'notice'    => Logger::NOTICE,
        'warning'   => Logger::WARNING,
        'error'     => Logger::ERROR,
        'critical'  => Logger::CRITICAL,
        'alert'     => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];
    
    /**
     * Magic method to call methods on the Logger instance
     * @param  string $name
     * @param  array $arguments
     * @return 
     */
    public static function __callStatic($name, $arguments)
    {
        if (empty(self::$logger)) {
            self::initLogger();
        }

        $arguments = array_map(function ($value) {
            return self::format($value);
        }, $arguments);

        if (method_exists(self::$logger, $name)) {
            return call_user_func_array([self::$logger, $name], $arguments);
        }

        throw new BadMethodCallException(sprintf('%s does not exist', $name));
    }

    /**
     * Init the Logger instance
     * @return void
     */
    protected static function initLogger()
    {
        self::$logger = new Logger('logger');
        self::$logger->pushHandler(
            new StreamHandler(
                sprintf(
                    '%s/%d-%d-%d.log',
                    rtrim(env('LOG_DIR',__DIR__.'/../../storage/logs/'), '/'),
                    date('Y'),
                    date('m'),
                    date('d')
                ),
                self::$logLevels[env('LOG_LEVEL', 'warning')]
            )
        );
    }

    /**
     * Handle arrays or objects passed as a message
     * @param  mixed $message
     * @return string
     */
    protected static function format($message)
    {
        if (is_array($message) or is_object($message)) {
            return var_export($message, true);
        }

        return $message;
    }
}
