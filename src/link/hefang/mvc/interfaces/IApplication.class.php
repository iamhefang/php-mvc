<?php
/**
 * Created by IntelliJ IDEA.
 * User: hefang
 * Date: 2018/12/3
 * Time: 07:41
 */

namespace link\hefang\mvc\interfaces;
defined("PHP_MVC") or exit(404);

use link\hefang\mvc\entities\Router;
use link\hefang\mvc\views\BaseView;

interface IApplication
{
    /**
     * @return array|null
     */
    function onInit();

    /**
     * @param string $path
     * @return Router|null
     */
    function onRequest(string $path);

    /**
     * @param \Throwable $e
     * @return BaseView|null
     */
    function onException(\Throwable $e);
}