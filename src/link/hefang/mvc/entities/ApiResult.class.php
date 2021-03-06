<?php

namespace link\hefang\mvc\entities;


use JsonSerializable;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\helpers\DebugHelper;

class ApiResult implements IJsonObject, IMapObject, JsonSerializable
{
	private $success = false;
	private $result = null;
	private $needLogin = false;
	private $needPassword = false;
	private $needAdmin = false;
	private $needSuperAdmin = false;
	private $needPermission = false;
	private $needUnlock = false;
	private $needDeveloper = false;

	/**
	 * ApiResult constructor.
	 * @param bool $success
	 * @param null $result
	 * @param bool $needLogin
	 * @param bool $needPassword
	 * @param bool $needAdmin
	 * @param bool $needSuperAdmin
	 * @param bool $needPermission
	 * @param bool $needUnlock
	 * @param bool $needDeveloper
	 */
	public function __construct(
		bool $success = false,
		$result = null,
		bool $needLogin = false,
		bool $needPassword = false,
		bool $needAdmin = false,
		bool $needSuperAdmin = false,
		bool $needPermission = false,
		bool $needUnlock = false,
		bool $needDeveloper = false
	)
	{
		$this->success = $success;
		$this->result = $result;
		$this->needLogin = $needLogin;
		$this->needPassword = $needPassword;
		$this->needAdmin = $needAdmin;
		$this->needSuperAdmin = $needSuperAdmin;
		$this->needPermission = $needPermission;
		$this->needUnlock = $needUnlock;
		$this->needDeveloper = $needDeveloper;
	}

	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->success;
	}

	/**
	 * @param bool $success
	 * @return ApiResult
	 */
	public function setSuccess(bool $success): ApiResult
	{
		$this->success = $success;
		return $this;
	}

	/**
	 * @return null
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @param null $result
	 * @return ApiResult
	 */
	public function setResult($result)
	{
		$this->result = $result;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedLogin(): bool
	{
		return $this->needLogin;
	}

	/**
	 * @param bool $needLogin
	 * @return ApiResult
	 */
	public function setNeedLogin(bool $needLogin): ApiResult
	{
		$this->needLogin = $needLogin;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedPassword(): bool
	{
		return $this->needPassword;
	}

	/**
	 * @param bool $needPassword
	 * @return ApiResult
	 */
	public function setNeedPassword(bool $needPassword): ApiResult
	{
		$this->needPassword = $needPassword;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedAdmin(): bool
	{
		return $this->needAdmin;
	}

	/**
	 * @param bool $needAdmin
	 * @return ApiResult
	 */
	public function setNeedAdmin(bool $needAdmin): ApiResult
	{
		$this->needAdmin = $needAdmin;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedSuperAdmin(): bool
	{
		return $this->needSuperAdmin;
	}

	/**
	 * @param bool $needSuperAdmin
	 * @return ApiResult
	 */
	public function setNeedSuperAdmin(bool $needSuperAdmin): ApiResult
	{
		$this->needSuperAdmin = $needSuperAdmin;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedPermission(): bool
	{
		return $this->needPermission;
	}

	/**
	 * @param bool $needPermission
	 * @return ApiResult
	 */
	public function setNeedPermission(bool $needPermission): ApiResult
	{
		$this->needPermission = $needPermission;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedUnlock(): bool
	{
		return $this->needUnlock;
	}

	/**
	 * @param bool $needUnlock
	 * @return ApiResult
	 */
	public function setNeedUnlock(bool $needUnlock): ApiResult
	{
		$this->needUnlock = $needUnlock;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNeedDeveloper(): bool
	{
		return $this->needDeveloper;
	}

	/**
	 * @param bool $needDeveloper
	 * @return ApiResult
	 */
	public function setNeedDeveloper(bool $needDeveloper): ApiResult
	{
		$this->needDeveloper = $needDeveloper;
		return $this;
	}


	public function toJsonString(): string
	{
		return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
	}

	public function toMap(): array
	{
		$map = [
			"success" => $this->success,
			"result" => $this->result
		];
		if ($this->needLogin) {
			$map["needLogin"] = true;
		}
		if ($this->needPassword) {
			$map["needPassword"] = true;
		}
		if ($this->needAdmin) {
			$map["needAdmin"] = true;
		}
		if ($this->needSuperAdmin) {
			$map["needSuperAdmin"] = true;
		}
		if ($this->needPermission) {
			$map["needPermission"] = true;
		}
		if ($this->needUnlock) {
			$map["needUnlock"] = true;
		}
		if ($this->needDeveloper) {
			$map["needDeveloper"] = true;
		}
		DebugHelper::apiDebugField($map);
		return $map;
	}

	public function jsonSerialize()
	{
		return $this->toMap();
	}
}
