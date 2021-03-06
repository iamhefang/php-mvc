<?php

namespace link\hefang\mvc\exceptions;


use link\hefang\mvc\entities\Router;
use RuntimeException;

class ActionNotFoundException extends RuntimeException
{
	public function __construct(Router $router)
	{
		parent::__construct($router->toJsonString());
	}
}
