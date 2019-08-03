<?php

namespace link\hefang\mvc\databases;


use link\hefang\helpers\ObjectHelper;

class Sql
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


}
