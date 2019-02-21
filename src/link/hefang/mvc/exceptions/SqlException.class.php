<?php

namespace link\hefang\mvc\exceptions;

use Throwable;

defined('PROJECT_NAME') or die("Access Refused");

class SqlException extends \Exception
{
    protected $sql = null;

    /**
     * SqlException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function newInstance($message = '', $sql = null): SqlException
    {
        $instance = new SqlException(is_string($message) ? $message : join(', ', $message));
        $instance->sql = $sql;
        return $instance;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }
}