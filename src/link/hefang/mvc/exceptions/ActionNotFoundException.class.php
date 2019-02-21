<?php
/**
 * Created by IntelliJ IDEA.
 * User: hefang
 * Date: 2018/12/7
 * Time: 15:56
 */

namespace link\hefang\mvc\exceptions;


use link\hefang\mvc\entities\Router;

class ActionNotFoundException extends \RuntimeException
{
    public function __construct(Router $router)
    {
        parent::__construct($router->toJsonString());
    }
}