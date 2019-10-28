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
	 * @return array|null
	 */
	function onInit();

	/**
	 * @param string $path
	 * @return Router|null
	 */
	function onRequest(string $path);

	/**
	 * @param Throwable $e
	 * @return BaseView|null
	 */
	function onException(Throwable $e);

	/**
	 * 根据token获取当前系统登录用户
	 * @param string $token
	 * @return BaseLoginModel|null|false
	 * 成功获取到用户返回用户信息
	 * 没有获取到返回null
	 * 不实现该方法由框架处理返回false
	 */
	function getLoginByToken(string $token);
}
