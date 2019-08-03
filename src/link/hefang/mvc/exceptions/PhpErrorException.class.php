<?php

namespace link\hefang\mvc\exceptions;


use Exception;

class PhpErrorException extends Exception
{

	/**
	 * PhpErrorException constructor.
	 * @param int $errno
	 * @param string $errstr
	 * @param string|null $errfile
	 * @param int $errline
	 */
	public function __construct(int $errno, string $errstr, string $errfile = null, int $errline = -1)
	{
		$this->code = $errno;
		$this->message = $errstr;
		$this->file = $errfile;
		$this->line = $errline;
	}
}
