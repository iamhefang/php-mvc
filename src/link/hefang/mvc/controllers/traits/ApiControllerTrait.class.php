<?php


namespace link\hefang\mvc\controllers\traits;


use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\mvc\controllers\BaseController;
use link\hefang\mvc\entities\ApiResult;
use link\hefang\mvc\exceptions\TraitException;
use link\hefang\mvc\Mvc;
use link\hefang\mvc\views\BaseView;
use link\hefang\mvc\views\TextView;
use Throwable;

trait ApiControllerTrait
{
//	public function __construct()
//	{
//		if (!is_subclass_of($this, BaseController::class)) {
//			throw new TraitException("无法在类\"" . __CLASS__ . "\"中使用\"" . self::class . "\"");
//		}
//	}


	public function _api(ApiResult $result): BaseView
	{
		ObjectHelper::checkNull($result);
		return $this->_text($result->toJsonString(), TextView::JSON);
	}

	public function _apiSuccess($result = "ok"): BaseView
	{
		return $this->_api(new ApiResult(true, $result));
	}

	public function _apiFailed(
		string $reason,
		bool $needLogin = false
		, bool $needPassword = false
		, bool $needAdmin = false
		, bool $needSuperAdmin = false
		, bool $needPermission = false
		, bool $needUnlock = false
		, bool $needDeveloper = false
	): BaseView
	{
		return $this->_api(new ApiResult(
			false, $reason, $needLogin,
			$needPassword, $needAdmin, $needSuperAdmin,
			$needPermission, $needUnlock, $needDeveloper
		));
	}

	public function _needLogin(string $message = null): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "您当前未登录或登录已超时, 请登录后重试" : $message;
		return $this->_apiFailed($message, true);
	}

	public function _needSuperAdmin(string $message = null): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "当前功能只有超级管理员才能使用" : $message;
		return $this->_apiFailed($message, false, false, true, true);
	}

	public function _needAdmin(string $message = null): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "当前功能只有管理员才能使用" : $message;
		return $this->_apiFailed($message, false, false, true);
	}

	public function _needPassword(string $message = "请输入密码"): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "请输入密码" : $message;
		return $this->_apiFailed($message, false, true);
	}

	public function _needPermission(string $message = null): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "您无权使用该功能" : $message;
		return $this->_apiFailed($message, false, false, false, false, true);
	}

	public function _needUnlock(string $message = null): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "您已锁屏, 请先解锁" : $message;
		return $this->_apiFailed($message, false, false, false, false, false, true);
	}

	public function _needDeveloper(string $message = null): BaseView
	{
		$message = StringHelper::isNullOrBlank($message) ? "该功能正在开发中" : $message;
		return $this->_apiFailed($message, false, false, false, false, false, false, true);
	}

	public function _exception(Throwable $e, string $message = null, string $title = null): BaseView
	{
		Mvc::getLogger()->error($title ?: '出现异常', $e->getMessage(), $e);
		return $this->_apiFailed($message ?: $e->getMessage());
	}
}
