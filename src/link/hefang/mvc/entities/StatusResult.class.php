<?php


namespace link\hefang\mvc\entities;


use JsonSerializable;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\helpers\DebugHelper;

/**
 * 状态码视图
 * @package link\hefang\mvc\entities
 */
class StatusResult implements IJsonObject, IMapObject, JsonSerializable
{
	private $status = 200;
	private $message = '';
	private $result;

	/**
	 * CodeResult constructor.
	 * @param int $status 状态码
	 * @param string $message 消息
	 * @param string|array|null $result 响应内容
	 */
	public function __construct(int $status, string $message, $result)
	{
		$this->status = $status;
		$this->message = $message;
		$this->result = $result;
	}


	/**
	 * 获取状态码
	 * @return int 状态码
	 */
	public function getStatus(): int
	{
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus(int $status)
	{
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage(string $message)
	{
		$this->message = $message;
	}

	/**
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @param mixed $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	public function toJsonString(): string
	{
		return json_encode($this->toMap());
	}

	public function toMap(): array
	{
		$map = [
			'status' => $this->status,
			'message' => $this->message,
			'result' => $this->result
		];
		DebugHelper::apiDebugField($map);
		return $map;
	}

	public function jsonSerialize()
	{
		return $this->toMap();
	}
}
