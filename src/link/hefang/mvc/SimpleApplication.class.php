<?php

namespace link\hefang\mvc;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\mvc\controllers\BaseController;
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

	/**
	 * 根据token获取当前系统登录用户
	 * @param string $token
	 * @return BaseLoginModel|null|false
	 * 成功获取到用户返回用户信息
	 * 没有获取到返回null
	 * 不实现该方法由框架处理返回false
	 */
	function getLoginByToken(string $token)
	{
		return false;
	}

	/**
	 * 动作或控制器需要登录，但当前没有登录用户时返回的视图
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedLogin(BaseController $controller)
	{
		return null;
	}

	/**
	 * 动作或控制器需要解锁才能访问， 当前登录用户登录状态处于锁定状态
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedUnlock(BaseController $controller)
	{
		return null;
	}

	/**
	 * 动作或控制器要求需要当前登录用户为管理员才能访问
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedAdmin(BaseController $controller)
	{
		return null;
	}

	/**
	 * 动作或控制器要求需要当前登录用户为超级管理员才能访问
	 * @param BaseController $controller 当前控制器
	 * @return BaseView|null
	 */
	function onNeedSuperAdmin(BaseController $controller)
	{
		return null;
	}
}
