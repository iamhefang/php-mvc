<?php


namespace link\hefang\mvc\controllers\traits;


use link\hefang\helpers\CollectionHelper;
use link\hefang\mvc\entities\StatusResult;
use link\hefang\mvc\Mvc;
use link\hefang\mvc\views\StatusView;
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
	 * @return StatusView
	 */
	public function _restApi($data, int $code, string $message = null): StatusView
	{
		$message = $message ?: CollectionHelper::getOrDefault(StatusView::HTTP_STATUS_CODE, $code, $message || "");
		return new StatusView(new StatusResult($code, $message, $data));
	}

	/**
	 * 200 请求成功
	 * @param mixed $data 数据
	 * @return StatusView
	 */
	public function _restApiOk($data = "ok"): StatusView
	{
		return $this->_restApi($data, 200);
	}

	/**
	 * 201 资源创建成功
	 * @param string $data
	 * @return StatusView
	 */
	public function _restApiCreated($data = "创建成功"): StatusView
	{
		return $this->_restApi($data, 201);
	}

	/**
	 * 304 数据未被更改
	 * @param string $data
	 * @return StatusView
	 */
	public function _restNotModified($data = "数据未被更改"): StatusView
	{
		return $this->_restApi($data, 304);
	}

	/**
	 * 400 请求参数不正确
	 * @param string $data
	 * @return StatusView
	 */
	public function _restApiBadRequest($data = "请求参数不正确"): StatusView
	{
		return $this->_restApi($data, 400);
	}

	/**
	 * 401 当前需要登录
	 * @param string $result
	 * @return StatusView
	 */
	public function _restApiUnauthorized($result = "您需要登录后才能访问"): StatusView
	{
		return $this->_restApi($result, 401);
	}

	/**
	 * 403 无权访问
	 * @param string $data
	 * @return StatusView
	 */
	public function _restApiForbidden($data = "无权访问"): StatusView
	{
		return $this->_restApi($data, 403);
	}

	/**
	 * 404 内容不存在
	 * @param string $data
	 * @return StatusView
	 */
	public function _restApiNotFound($data = "访问的接口不存在"): StatusView
	{
		return $this->_restApi($data, 404);
	}

	/**
	 * 405 请求访求不允许
	 * @param string $data message
	 * @return StatusView
	 */
	public function _methodNotAllowed($data = "请求方法不正确"): StatusView
	{
		return $this->_restApi($data, 405);
	}

	/**
	 * 423 当前用户已被锁定, 或当前访问的资源已被锁定
	 * @param string $data
	 * @return StatusView
	 */
	public function _restApiLocked($data = "已被锁定")
	{
		return $this->_restApi($data, 423);
	}

	/**
	 * 499 php-mvc扩展自定义错误码，未知原因，数据处理失败
	 * @param string $data
	 * @return StatusView
	 */
	public function _restFailedUnknownReason($data = "未知原因，数据处理失败")
	{
		return $this->_restApi($data, 499);
	}

	/**
	 * 500 服务端出现异常
	 * @param Throwable $exception
	 * @param string $data
	 * @return StatusView
	 */
	public function _restApiServerError(Throwable $exception, $data = "服务端错误"): StatusView
	{
		Mvc::getLogger()->error(null, $data, $exception);
		return $this->_restApi($data, 500);
	}

}
