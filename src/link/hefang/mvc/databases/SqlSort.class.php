<?php

namespace link\hefang\mvc\databases;


class SqlSort
{
	const TYPE_DEFAULT = "";
	const TYPE_ASC = "ASC";//升序
	const TYPE_DESC = "DESC";//降序
	private $key = "";
	private $type = "";
	private $nullsFirst = false;

	/**
	 * SqlSort constructor.
	 * @param string $key
	 * @param string $type
	 * @param bool $nullsFirst
	 */
	public function __construct(string $key = null, string $type = null, bool $nullsFirst = null)
	{
		$this->key = $key;
		if ($key) {
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

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setKey(string $key): SqlSort
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type): SqlSort
	{
		$this->type = $type;
		return $this;
	}

}
