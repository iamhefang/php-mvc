<?php


namespace link\hefang\mvc\models;


use link\hefang\helpers\StringHelper;

class ModelField
{
	const TRIM_DEF_CHARLIST = " \t\n\r \v";
	const TYPE_TEXT = "text";
	const TYPE_INT = "int";
	const TYPE_FLOAT = "float";
	const TYPE_BOOL = "bool";
	const TYPE_JSON = "json";
	const TYPE_JSON_STRING = "json-string";

	//是否主键
	private $primaryKey = false;
	//是否自增字段
	private $autoIncrement = false;

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
	public function __construct(string $prop, string $field = null, string $type = self::TYPE_TEXT, string $trim = "")
	{
		$this->field = $field ?: StringHelper::hump2underLine($prop);
		$this->prop = $prop;
		$this->type = $type;
		$this->trim = $trim;
	}

	public static function prop(string $prop): ModelField
	{
		return new ModelField($prop);
	}

	public function field(string $field): ModelField
	{
		$this->field = $field;
		return $this;
	}

	public function type(string $type): ModelField
	{
		$this->type = $type;
		return $this;
	}

	public function trim(string $trim = ModelField::TRIM_DEF_CHARLIST): ModelField
	{
		$this->trim = $trim;
		return $this;
	}

	public function hide(): ModelField
	{
		$this->hideInResult = true;
		return $this;
	}

	public function primaryKey(): ModelField
	{
		$this->primaryKey = true;
		return $this;
	}

	public function autoIncrement(): ModelField
	{
		$this->autoIncrement = true;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPrimaryKey(): bool
	{
		return $this->primaryKey;
	}

	/**
	 * @param bool $primaryKey
	 * @return ModelField
	 */
	public function setPrimaryKey(bool $primaryKey): ModelField
	{
		$this->primaryKey = $primaryKey;
		return $this;
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
	public function setTrim(string $trim = ModelField::TRIM_DEF_CHARLIST): ModelField
	{
		$this->trim = $trim;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isAutoIncrement(): bool
	{
		return $this->autoIncrement;
	}

	/**
	 * @param bool $autoIncrement
	 * @return ModelField
	 */
	public function setAutoIncrement(bool $autoIncrement): ModelField
	{
		$this->autoIncrement = $autoIncrement;
		return $this;
	}

}
