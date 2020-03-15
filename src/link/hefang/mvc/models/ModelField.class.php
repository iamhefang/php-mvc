<?php


namespace link\hefang\mvc\models;


class ModelField
{
	const TYPE_TEXT = "text";
	const TYPE_INT = "int";
	const TYPE_BOOL = "bool";

	//数据库中对应的字段
	private $field = "";
	//模型对应字段名
	private $prop = "";
	//数据类型
	private $type = "";
	//要trim的字段
	private $trim = "";
	//在结果中不显示该字段
	private $hideInResult = false;

	/**
	 * ModelField constructor.
	 * @param string $field
	 * @param string $prop
	 * @param string $type
	 * @param string $trim
	 */
	public function __construct(string $prop, string $field = null, string $type = self::TYPE_TEXT, string $trim = "\n\r \0")
	{
		$this->field = $field ?: $prop;
		$this->prop = $prop;
		$this->type = $type;
		$this->trim = $trim;
	}

	/**
	 * @return bool
	 */
	public function isHideInResult(): bool
	{
		return $this->hideInResult;
	}

	/**
	 * @param bool $hideInResult
	 * @return ModelField
	 */
	public function setHideInResult(bool $hideInResult): ModelField
	{
		$this->hideInResult = $hideInResult;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getField(): string
	{
		return $this->field;
	}

	/**
	 * @param string $field
	 * @return ModelField
	 */
	public function setField(string $field): ModelField
	{
		$this->field = $field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getProp(): string
	{
		return $this->prop;
	}

	/**
	 * @param string $prop
	 * @return ModelField
	 */
	public function setProp(string $prop): ModelField
	{
		$this->prop = $prop;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return ModelField
	 */
	public function setType(string $type): ModelField
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTrim(): string
	{
		return $this->trim;
	}

	/**
	 * @param string $trim
	 * @return ModelField
	 */
	public function setTrim(string $trim): ModelField
	{
		$this->trim = $trim;
		return $this;
	}

}
