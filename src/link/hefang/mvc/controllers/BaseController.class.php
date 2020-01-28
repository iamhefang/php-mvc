<?php

namespace link\hefang\mvc\controllers;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\mvc\controllers\traits\ApiControllerTrait;
use link\hefang\mvc\controllers\traits\NotApiControllerTrait;
use link\hefang\mvc\controllers\traits\RestApiControllerTrait;
use link\hefang\mvc\databases\SqlSort;
use link\hefang\mvc\entities\Router;
use link\hefang\mvc\interfaces\IController;
use link\hefang\mvc\models\BaseLoginModel;
use link\hefang\mvc\Mvc;
use RuntimeException;

/**
 * 控制器基类，所有的控制器都要直接或间接继承该类
 * @package link\hefang\mvc\controllers
 */
abstract class BaseController implements IController
{
	use NotApiControllerTrait;
	use ApiControllerTrait;
	use RestApiControllerTrait;

	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var BaseLoginModel
	 */
	private $currentLogin = null;
	private $___post = [];
	private $___request = [];

	/**
	 * 返回控制器所属模块名
	 * @return string
	 */
	public static function module(): string
	{
		$class = explode("\\", get_called_class());
		return $class[count($class) - 3];
	}

	/**
	 * 返回控制器名称
	 * 如果控制器实现类重写了该方法
	 * @return string
	 */
	public static function name(): string
	{
		$class = get_called_class();
		if ($class === BaseController::class) {
			throw new RuntimeException("请不要直接使用BaseController::class");
		}
		$class = explode("\\", $class);
		return str_replace("Controller", "", CollectionHelper::last($class, ""));
	}

	/**
	 * 标记该类是否是一个控制器
	 * @return bool
	 */
	public static function isController(): bool
	{
		return true;
	}

	/**
	 * 获取当前路由信息
	 * @return Router
	 */
	public function getRouter(): Router
	{
		return $this->router;
	}

	/**
	 * 设置当前路由信息
	 * @param Router $router
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;
	}


	/**
	 * @param string $name
	 * @param null|string|int|float $defaultValue
	 * @return mixed
	 */
	public function _get(string $name, $defaultValue = null)
	{
		ObjectHelper::checkNull($name);
		return CollectionHelper::getOrDefault($_GET, $name, $defaultValue);
	}

	/**
	 * @param string $name
	 * @param null|string|int|float $defaultValue
	 * @return mixed
	 */
	public function _post(string $name, $defaultValue = null)
	{
		ObjectHelper::checkNull($name);
		return CollectionHelper::getOrDefault($this->___post, $name, $defaultValue);
	}

	/**
	 * @param string $name
	 * @param null|string|int|float $defaultValue
	 * @return mixed
	 */
	public function _cookie(string $name, $defaultValue = null)
	{
		ObjectHelper::checkNull($name);
		return CollectionHelper::getOrDefault($_COOKIE, $name, $defaultValue);
	}

	/**
	 * @param string $name
	 * @param null|string|int|float $defaultValue
	 * @return mixed
	 */
	public function _request(string $name, $defaultValue = null)
	{
		ObjectHelper::checkNull($name);
		return CollectionHelper::getOrDefault($this->___request, $name, $defaultValue);
	}

	/**
	 * 读取session
	 * @param string $name
	 * @param $defaultValue
	 * @return mixed|null
	 */
	public function _session(string $name, $defaultValue = null)
	{
		isset($_SESSION) or session_start();
		return CollectionHelper::getOrDefault($_SESSION, $name, $defaultValue);
	}

	public function _setSession(string $name, $value): BaseController
	{
		isset($_SESSION) or session_start();
		$_SESSION[$name] = $value;
		return $this;
	}

	/**
	 * 获取指定请求头
	 * @param string $name 要获取请求头的键
	 * @param string|null $defaultValue 无对应值时返回默认值
	 * @return string|null 返回值
	 */
	public function _header(string $name, string $defaultValue = null)
	{
		$name = "HTTP_" . strtoupper(str_replace("-", "_", $name));
		$name2 = 'REDIRECT_' . $name;

		return isset($_SERVER[$name]) ? $_SERVER[$name] : (
		isset($_SERVER[$name2]) ? $_SERVER[$name2] : $defaultValue);
	}

	/**
	 * 获取当前请求的方法
	 * @return string
	 */
	public function _method(): string
	{
		return strtoupper($this->_header("method"));
	}

	/**
	 * 获取当前登录用户
	 * @return BaseLoginModel|null
	 */
	public function _getLogin()
	{
		$authType = Mvc::getAuthType();
		if ($authType === "TOKEN" || $authType === "BOTH") {
			$token = $this->_header("Authorization");
			if (StringHelper::isNullOrBlank($token)) {
				return null;
			}
			$login = Mvc::getApplication()->getLoginByToken($token);
			if ($login instanceof BaseLoginModel) {
				return $login;
			}
			$login = $login === false ? Mvc::getCache()->get($token, null) : null;
			if ($login instanceof BaseLoginModel) {
				return $login;
			}
		}
		return $authType === "TOKEN" ? null : $this->_session(BaseLoginModel::LOGIN_SESSION_KEY);
	}

	/**
	 * 获取分页请求时的页码
	 * @param int $defaultValue 不传默认第一页
	 * @return int
	 */
	public function _pageIndex(int $defaultValue = 1): int
	{
		$name = Mvc::getProperty("project.pagination.index.key", "pageIndex");
		return intval($this->_request($name, $defaultValue));
	}

	/**
	 * 获取分页请求时的页大小
	 * @param int $defaultValue 不传默认读取配置文件中的数据
	 * @return int
	 */
	public function _pageSize(int $defaultValue = 10): int
	{
		$name = Mvc::getProperty("project.pagination.size.key", "pageSize");
		return intval($this->_request($name, $defaultValue));
	}

	/**
	 * 获取数据列表请求时的排序信息
	 * @param string|null $key
	 * @param string $type
	 * @param bool $nullsFirst
	 * @return SqlSort|null
	 */
	public function _sort(
		string $key = null,
		string $type = null,
		bool $nullsFirst = null)
	{
		$keyName = Mvc::getProperty("project.sort.key.name", "sortKey");
		$typeName = Mvc::getProperty("project.sort.key.name", "sortType");
		$key = $this->_request($keyName, $key);
		$type = $this->_request($typeName, $type ?: '');
		if (StringHelper::isNullOrBlank($key)) {
			return null;
		}
		return new SqlSort($key, $type, $nullsFirst);
	}

	/**
	 * 获取当前访问客户端的ip地址
	 * @return string
	 */
	public function _ip(): string
	{
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = "unknown";
		}
		return $ip;
	}

	/**
	 * 获取用户代理字符串
	 * @return string
	 */
	public function _userAgent(): string
	{
		return $this->_header("User-Agent");
	}
}
