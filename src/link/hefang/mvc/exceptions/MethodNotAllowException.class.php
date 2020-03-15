<?php


namespace link\hefang\mvc\exceptions;


use link\hefang\mvc\entities\Router;
use RuntimeException;
use Throwable;

class MethodNotAllowException extends RuntimeException
{
	private $router;

	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return Router
	 */
	public function getRouter(): Router
	{
		return $this->router;
	}

	/**
	 * @param Router $router
	 * @return MethodNotAllowException
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;
		return $this;
	}

}
