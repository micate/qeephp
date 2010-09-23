<?php

namespace qeephp;

/**
 * QeePHP 所有异常的基础类
 */
class BaseError extends \Exception
{
    const NOT_IMPLEMENTED       = 0x9991;
    const TYPE_MISMATCH         = 0x9992;
    const NOT_CALLABLE          = 0x9993;
    const INVALID_PARAMETERS    = 0x9994;
    const UNKNOWN_ERROR         = 0x9995;

    function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    static function not_implemented_error($method_name)
    {
        $class = get_called_class();
        return new $class("NOT_IMPLEMENTED: {$method_name}", self::NOT_IMPLEMENTED);
    }

    static function type_mismatch_error($expected_type, $actual_type)
    {
        $class = get_called_class();
        return new $class("TYPE_MISMATCH: {$expected_type}, {$actual_type}", self::TYPE_MISMATCH);
    }

    static function not_callable_error()
    {
        $class = get_called_class();
        return new $class("NOT_CALLABLE", self::NOT_CALLABLE);
    }

    static function invalid_parameters_error($message)
    {
        $class = get_called_class();
        return new $class($message, self::INVALID_PARAMETERS);
    }

    static function unknown_error($message)
    {
        $class = get_called_class();
        return new $class($message, self::UNKNOWN_ERROR);
    }
}

