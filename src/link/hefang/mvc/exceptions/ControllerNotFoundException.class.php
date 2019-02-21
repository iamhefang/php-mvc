<?php
/**
 * Created by IntelliJ IDEA.
 * User: hefang
 * Date: 2018/12/7
 * Time: 15:54
 */

namespace link\hefang\mvc\exceptions;


use link\hefang\mvc\entities\Router;

class ControllerNotFoundException extends \RuntimeException
{
    public function __construct(Router $router)
    {
        parent::__construct($router->toJsonString());
    }
}