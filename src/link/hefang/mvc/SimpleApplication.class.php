<?php

namespace link\hefang\mvc;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\mvc\entities\Router;
use link\hefang\mvc\interfaces\IApplication;
use link\hefang\mvc\views\BaseView;

class SimpleApplication implements IApplication
{

    /**
     * @return array|null
     */
    function onInit()
    {
        return null;
    }

    /**
     * @param string $path
     * @return Router|null
     */
    function onRequest(string $path)
    {
        return null;
    }

    /**
     * @param \Throwable $e
     * @return BaseView|null
     */
    function onException(\Throwable $e)
    {
        return null;
    }
}