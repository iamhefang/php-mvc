<?php

namespace link\hefang\mvc\interfaces;
defined("PHP_MVC") or exit(404);

use link\hefang\mvc\entities\Router;
use link\hefang\mvc\models\BaseLoginModel;
use link\hefang\mvc\views\BaseView;
use Throwable;

interface IApplication
{
	/**
	 * 框架初始化时回调
	 * @return array|null
	 */
	function onInit();

	/**
	 * 收到请求时回调
	 * @param string $path
	 * @return Router|null
	 */
	function onRequest(string $path);

	/**
	 * 出现未捕获异常时回调
	 * @param Throwable $e
	 * @return BaseView|null
	 */
	function onException(Throwable $e);
}
