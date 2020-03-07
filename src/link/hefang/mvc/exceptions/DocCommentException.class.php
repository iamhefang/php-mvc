<?php


namespace link\hefang\mvc\exceptions;


use Exception;
use Throwable;

class DocCommentException extends Exception
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
