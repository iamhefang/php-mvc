<?php

namespace link\hefang\mvc;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\mvc\entities\Router;
use link\hefang\mvc\interfaces\IApplication;
use link\hefang\mvc\models\BaseLoginModel;
use link\hefang\mvc\views\BaseView;
use Throwable;

class SimpleApplication implements IApplication
{

	/**
	 * 在系统初始化时回调该方法
	 * @return array|null
	 */
	function onInit()
	{
		return null;
	}

	/**
	 * 在任何请求到达框架时都会回调该方法
	 * @param string $path 请求的路径
	 * @return Router|null 要返回的路由信息
	 */
	function onRequest(string $path)
	{
		return null;
	}

	/**
	 * 在出现未捕获异常时会回调该方法
	 * @param Throwable $e
	 * @return BaseView|null
	 */
	function onException(Throwable $e)
	{
		return null;
	}
}
