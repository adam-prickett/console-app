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

    public function __construct()
    {
        if (!isset($logLevels[env('LOG_LEVEL', 'warning')])) {
            throw new UnexpectedValueException('Invalid log level provided');
        }

        self::initLogger();
    }

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

    // public static function debug($message)
    // {
    //     self::log($message, 'debug');
    // }

    // public static function info($message)
    // {
    //     self::log($message, 'info');
    // }

    // public static function notice($message)
    // {
    //     self::log($message, 'notice');
    // }

    // public static function warning($message)
    // {
    //     self::log($message, 'warning');
    // }

    // public static function error($message)
    // {
    //     self::log($message, 'error');
    // }

    // public static function critical($message)
    // {
    //     self::log($message, 'critical');
    // }

    // public static function alert($message)
    // {
    //     self::log($message, 'alert');
    // }

    // public static function emergency($message)
    // {
    //     self::log($message, 'emergency');
    // }

    protected static function log($message, $level)
    {
        if (empty(self::$logger)) {
            self::initLogger();
        }
        
        self::$logger->{$level}($message);
    }

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
