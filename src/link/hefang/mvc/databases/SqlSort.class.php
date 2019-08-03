<?php

namespace link\hefang\mvc\databases;


use link\hefang\helpers\ObjectHelper;

class SqlSort
{
	private $key = "";
	private $type = "";
	private $nullsFirst = false;

	const TYPE_DEFAULT = "";
	const TYPE_ASC = "ASC";
	const TYPE_DESC = "DESC";

	/**
	 * SqlSort constructor.
	 * @param string $key
	 * @param string $type
	 * @param bool $nullsFirst
	 */
	public function __construct(string $key, string $type = null, bool $nullsFirst = null)
	{
		ObjectHelper::checkNull($key);
		$this->key = $key;
		if (strcasecmp($type, "DESC") === 0) {
			$this->type = self::TYPE_DESC;
			$this->nullsFirst = $nullsFirst === null ? false : $nullsFirst;
		} else if (strcasecmp($type, "ASC") === 0) {
			$this->type = self::TYPE_ASC;
			$this->nullsFirst = $nullsFirst === null ? true : $nullsFirst;
		} else {
			$this->type = self::TYPE_DEFAULT;
		}
	}

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function isNullsFirst(): bool
	{
		return $this->nullsFirst;
	}

}
