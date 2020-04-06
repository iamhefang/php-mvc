<?php

namespace link\hefang\mvc\entities;


use JsonSerializable;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;

/**
 * Class Pager
 * @package link\hefang\mvc\entities
 */
class Pager implements IJsonObject, IMapObject, JsonSerializable
{
	private $total = 0;
	private $current = 1;
	private $size = 20;
	private $data = [];

	/**
	 * Pager constructor.
	 * @param int $total
	 * @param int $pageIndex
	 * @param int $pageSize
	 * @param array $data
	 */
	public function __construct(int $total, int $pageIndex, int $pageSize, array $data)
	{
		$this->total = $total;
		$this->current = $pageIndex;
		$this->size = $pageSize;
		$this->data = $data;
	}

	/**
	 * @return int
	 */
	public function getTotal(): int
	{
		return $this->total;
	}

	/**
	 * @return int
	 */
	public function getCurrent(): int
	{
		return $this->current;
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	public function toJsonString(): string
	{
		return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
	}

	public function toMap(): array
	{
		return [
			"current" => $this->current,
			"size" => $this->size,
			"total" => $this->total,
			"data" => $this->data
		];
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->toMap();
	}
}
