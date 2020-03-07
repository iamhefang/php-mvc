<?php


namespace link\hefang\mvc\exceptions;


use RuntimeException;

class TraitException extends RuntimeException
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
