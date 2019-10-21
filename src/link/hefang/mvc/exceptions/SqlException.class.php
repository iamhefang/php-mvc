<?php

namespace link\hefang\mvc\exceptions;
defined('PHP_MVC') or die("Access Refused");

use Exception;
use Throwable;


class SqlException extends Exception
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
