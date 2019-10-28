<?php


namespace link\hefang\mvc\exceptions;


class TraitException extends \RuntimeException
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
