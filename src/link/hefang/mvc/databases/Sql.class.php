<?php

namespace link\hefang\mvc\databases;


use JsonSerializable;
use link\hefang\helpers\ObjectHelper;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;

class Sql implements IJsonObject, JsonSerializable, IMapObject
{
	private $sql = '';
	private $params = [];

	/**
	 * Sql constructor.
	 * @param string $sql
	 * @param array $params
	 */
	public function __construct(string $sql, array $params = null)
	{
		$this->sql = $sql;
		$this->params = ObjectHelper::nullOrDefault($params, []);
	}

	/**
	 * @return string
	 */
	public function getSql(): string
	{
		return $this->sql;
	}

	/**
	 * @param string $sql
	 * @return Sql
	 */
	public function setSql(string $sql): Sql
	{
		$this->sql = $sql;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @param array $params
	 * @return Sql
	 */
	public function setParams(array $params): Sql
	{
		$this->params = $params;
		return $this;
	}


	public function toJsonString(): string
	{
		return json_encode($this->toMap());
	}

	public function toMap(): array
	{
		return [
			"sql" => $this->sql,
			"params" => $this->params
		];
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->toMap();
	}

	public function __toString()
	{
		$params = '';
		foreach ($this->getParams() as $key => $value) {
			$type = gettype($value);
			if (is_bool($value)) {
				$value = $value ? 'true' : 'false';
			}
			$params .= "\n\r\r{$key}({$type}): {$value}";
		}
		$params or $params = '无';
		return "SQL: {$this->getSql()}\n参数：{$params}";
	}
}
