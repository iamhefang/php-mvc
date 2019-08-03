<?php

namespace link\hefang\mvc\models;


use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\TimeHelper;
use link\hefang\mvc\controllers\BaseController;
use link\hefang\mvc\Mvc;

abstract class BaseLoginModel extends BaseModel
{
	/**
	 * @var string|null
	 */
	protected $loginIp = null;
	/**
	 * @var int|null
	 */
	protected $loginTime = 0;
	/**
	 * @var string|null
	 */
	protected $loginUserAgent = null;
	protected $isLockedScreen = false;
	/**
	 * @var string|null
	 */
	protected $tokenSessionId = '';

	private $lastActiveTime = 0;

	public $unlockTryCount = 5;

	const LOGIN_SESSION_KEY = "PHP_MVC_LOGIN_SESSION_KEY";

	/**
	 * @return null|string
	 */
	public function getLoginIp()
	{
		return $this->loginIp;
	}

	/**
	 * @param null|string $loginIp
	 * @return $this
	 */
	public function setLoginIp(string $loginIp)
	{
		$this->loginIp = $loginIp;
		return $this;
	}

	/**
	 * @return null|int
	 */
	public function getLoginTime()
	{
		return $this->loginTime;
	}

	/**
	 * @param float $loginTime
	 * @return $this
	 */
	public function setLoginTime(float $loginTime)
	{
		$this->loginTime = floor($loginTime);
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getLoginUserAgent()
	{
		return $this->loginUserAgent;
	}

	/**
	 * @param null|string $loginUserAgent
	 * @return $this
	 */
	public function setLoginUserAgent(string $loginUserAgent)
	{
		$this->loginUserAgent = $loginUserAgent;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isLockedScreen(): bool
	{
		return $this->isLockedScreen;
	}

	/**
	 * @param bool $isLockedScreen
	 * @return BaseLoginModel
	 */
	public function setIsLockedScreen(bool $isLockedScreen)
	{
		$this->isLockedScreen = $isLockedScreen;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getTokenSessionId(): string
	{
		return $this->tokenSessionId;
	}


	abstract public function isAdmin(): bool;

	abstract public function isSuperAdmin(): bool;

	public function isDeveloper()
	{
		return in_array($this->getId(), Mvc::getDevelopers());
	}

	/**
	 * @return string|null
	 */
	abstract public function getRoleName();

	/**
	 * @return string|null
	 */
	abstract public function getId();

	abstract public function setId(string $id);

	/**
	 * @return string|null
	 */
	abstract public function getRoleId();

	abstract public function setRoleId(string $roleId);

	/**
	 * @param BaseController $controller
	 * @return $this
	 */
	public function login(BaseController $controller)
	{
		ObjectHelper::checkNull($controller);
		$this->lastActiveTime = TimeHelper::currentTimeMillis();
		$this->setLoginTime(TimeHelper::currentTimeMillis())
			->setLoginIp($controller->_ip())
			->setLoginUserAgent($controller->_header("User-Agent"))
			->updateSession($controller);
		return $this;
	}

	public function updateSession(BaseController $controller)
	{
		ObjectHelper::checkNull($controller);
		$controller->_setSession(self::LOGIN_SESSION_KEY, $this);
	}

	public function logout(BaseController $controller)
	{
		ObjectHelper::checkNull($controller);
		$controller->_setSession(self::LOGIN_SESSION_KEY, null);
	}

	public function toMap(): array
	{
		$map = parent::toMap();
		$map['isSuperAdmin'] = $this->isSuperAdmin();
		$map['isAdmin'] = $this->isAdmin();
		$map['isDeveloper'] = $this->isDeveloper();
		$map['roleName'] = $this->getRoleName();
		$map['loginIp'] = $this->getLoginIp();
		$map['loginTime'] = TimeHelper::formatMillis("Y-m-d H:i:s", $this->getLoginTime());
		$map['loginUserAgent'] = $this->getLoginUserAgent();
		$map['roleId'] = $this->getRoleId();
		$map['isLockedScreen'] = $this->isLockedScreen();
		unset($map['password']);
		return $map;
	}

	public function __wakeup()
	{
		$this->lastActiveTime = TimeHelper::currentTimeMillis();
	}
}
