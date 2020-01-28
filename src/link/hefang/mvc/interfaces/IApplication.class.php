<?php

namespace link\hefang\mvc\interfaces;
defined("PHP_MVC") or exit(404);

use link\hefang\mvc\controllers\BaseController;
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

	/**
	 * 动作或控制器需要登录，但当前没有登录用户时返回的视图
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedLogin(BaseController $controller);

	/**
	 * 动作或控制器需要解锁才能访问， 当前登录用户登录状态处于锁定状态
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedUnlock(BaseController $controller);

	/**
	 * 动作或控制器要求需要当前登录用户为管理员才能访问
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedAdmin(BaseController $controller);

	/**
	 * 动作或控制器要求需要当前登录用户为超级管理员才能访问
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedSuperAdmin(BaseController $controller);
}
