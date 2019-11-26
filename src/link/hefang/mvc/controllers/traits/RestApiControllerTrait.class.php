<?php


namespace link\hefang\mvc\controllers\traits;


use link\hefang\helpers\CollectionHelper;
use link\hefang\mvc\entities\StatusResult;
use link\hefang\mvc\Mvc;
use link\hefang\mvc\views\BaseView;
use link\hefang\mvc\views\CodeView;
use Throwable;

/**
 * Trait RestApiControllerTrait
 * @package link\hefang\mvc\controllers\traits
 */
trait RestApiControllerTrait
{
//	public function __construct()
//	{
//		if (!is_subclass_of($this, BaseController::class)) {
//			throw new TraitException("无法在类\"" . __CLASS__ . "\"中使用\"" . RestApiControllerTrait::class . "\"");
//		}
//	}

	/**
	 * 返回rest风格接口数据
	 * @param mixed $data 数据
	 * @param int $code 状态码
	 * @param string $message http消息
	 * @return CodeView
	 */
	public function _restApi($data, int $code, string $message = null): CodeView
	{
		$message = $message ?: CollectionHelper::getOrDefault(CodeView::HTTP_STATUS_CODE, $code, $message || "");
		return new CodeView(new StatusResult($code, $message, $data));
	}

	/**
	 * 200 请求成功
	 * @param mixed $data 数据
	 * @return CodeView
	 */
	public function _restApiOk($data): CodeView
	{
		return $this->_restApi($data, 200);
	}

	/**
	 * 201 资源创建成功
	 * @param string $data
	 * @return CodeView
	 */
	public function _restApiCreated($data = "创建成功"): CodeView
	{
		return $this->_restApi($data, 201);
	}

	/**
	 * 400 请求参数不正确
	 * @param string $data
	 * @return CodeView
	 */
	public function _restApiBadRequest($data = "请求参数不正确"): CodeView
	{
		return $this->_restApi($data, 400);
	}

	/**
	 * 401 当前需要登录
	 * @param string $result
	 * @return BaseView
	 */
	public function _restApiUnauthorized($result = "您需要登录后才能访问")
	{
		return $this->_restApi($result, 401);
	}

	/**
	 * 403 无权访问
	 * @param string $data
	 * @return CodeView
	 */
	public function _restApiForbidden($data = "无权访问"): CodeView
	{
		return $this->_restApi($data, 403);
	}

	/**
	 * 404 内容不存在
	 * @param string $data
	 * @return CodeView
	 */
	public function _restApiNotFound($data = "访问的接口不存在"): CodeView
	{
		return $this->_restApi($data, 404);
	}

	/**
	 * 405 请求访求不允许
	 * @param string $data message
	 * @return CodeView
	 */
	public function _methodNotAllowed($data = "请求方法不正确"): CodeView
	{
		return $this->_restApi($data, 405);
	}

	/**
	 * 423 当前用户已被锁定, 或当前访问的资源已被锁定
	 * @param string $data
	 * @return CodeView
	 */
	public function _restApiLocked($data = "已被锁定")
	{
		return $this->_restApi($data, 423);
	}

	/**
	 * 500 服务端出现异常
	 * @param Throwable $exception
	 * @param string $data
	 * @return CodeView
	 */
	public function _restApiServerError(Throwable $exception, $data = "服务端错误"): CodeView
	{
		Mvc::getLogger()->error(null, $data, $exception);
		return $this->_restApi($data, 500);
	}

}
