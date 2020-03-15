<?php

namespace link\hefang\mvc\models;


use JsonSerializable;
use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\databases\BaseDb;
use link\hefang\mvc\databases\Mysql;
use link\hefang\mvc\databases\Sql;
use link\hefang\mvc\databases\SqlSort;
use link\hefang\mvc\entities\Pager;
use link\hefang\mvc\exceptions\ModelException;
use link\hefang\mvc\exceptions\SqlException;
use link\hefang\mvc\interfaces\IModel;
use link\hefang\mvc\Mvc;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;


abstract class BaseModel implements IJsonObject, IMapObject, IModel, JsonSerializable
{
	private $exist = false;

	/**
	 * 从数据库中读取一条数据
	 * @param string $id 主键值
	 * @return BaseModel
	 * @throws ModelException
	 * @throws SqlException
	 */
	public static function get(string $id): BaseModel
	{
		$class = get_called_class();
		if ($class === BaseModel::class) {
			throw new ModelException("不要直接调用BaseModel::get, 请使用具体模型类::get");
		}
		$database = self::_database();
		$table = self::_table();
		$primaryKeyFields = self::_primaryKeyFields();

		if (!is_array($primaryKeyFields) || count($primaryKeyFields) === 0) {
			throw new ModelException('模型' . $class . '未设置主键');
		}

		if (is_array($primaryKeyFields) && count($primaryKeyFields) > 1) {
			throw new ModelException("多个主键的请使用find方法");
		}

		$pk = $primaryKeyFields[0];

		$row = $database->row($table, Mysql::addQuotes($pk) . " = '$id'");

		return self::row2model($row);
	}

	private static function _database(): BaseDb
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	private static function _table()
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	private static function _primaryKeyFields()
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	private static function row2model(array $row = null): BaseModel
	{
		$class = get_called_class();

		$needTrimFields = self::_needTrimFields();

		$model = new $class();
		$row or $row = [];
		try {
			$ref = new ReflectionClass($class);
			foreach ($row as $key => $value) {
				$fieldName = CollectionHelper::getOrDefault(self::_fields(), $key);
				if (!$fieldName) continue;
				$field = $ref->getProperty($fieldName);
				if ($field === null) continue;
				$field->setAccessible(true);
				$field->setValue($model, in_array($fieldName, $needTrimFields) ? trim($value) : $value);
			}
			if (count($row) > 0) {
				$exist = ObjectHelper::getProperty($class, "exist");
				if ($exist) {
					$exist->setAccessible(true);
					$exist->setValue($model, true);
				}
			}
			return $model;
		} catch (ReflectionException $e) {
			Mvc::getLogger()->error("实例化模型'$class'异常", $e->getMessage(), $e);
			return $model;
		}
	}

	private static function _needTrimFields()
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	private static function _fields(): array
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		$fields = $class::$method();
		$finalFields = [];
		foreach ($fields as $field) {
			if ($field instanceof ModelField) {
				$finalFields[$field->getField()] = $field->getProp();
			} else if (is_string($field)) {
				$finalFields[$field] = $field;
			}
		}
		foreach ($fields as $key => $value) {
			$finalFields[is_numeric($key) ? $value : $key] = $value;
		}
		return $finalFields;
	}

	public static function prop2field(string $prop)
	{
		self::_checkCalledClass(__FUNCTION__);
		return CollectionHelper::getOrDefault(array_flip(self::_fields()), $prop, $prop);
	}

	/**
	 *
	 * @param string|null $query
	 * @return string
	 */
	public static function query2sql($query): string
	{
		self::_checkCalledClass(__FUNCTION__);
		if (StringHelper::isNullOrBlank($query)) return "";

		$query = preg_replace_callback("/([-a-z0-9_]+)(=|~=|!=|>|<|>=|<=)([^!%&|~=)(]+)/i", function (array $match) {
			$field = self::prop2field($match[1]);
			if ($match[2] === "~=") {
				return "(`{$field}` LIKE '%{$match[3]}' OR `{$field}` LIKE '%{$match[3]}%' OR `{$field}` LIKE '{$match[3]}%')";
			}
			return "`{$field}`{$match[2]}'{$match[3]}'";
		}, $query);
		$query = str_replace("&", " AND ", $query);
		$query = str_replace("|", " OR ", $query);
		return $query;
	}

	/**
	 * @param $sort
	 * @return SqlSort[]|null
	 */
	public static function sort2sql($sort)
	{
		self::_checkCalledClass(__FUNCTION__);
		if (StringHelper::isNullOrBlank($sort)) return null;
//		$class = get_called_class();
		$fields = explode(",", $sort);
		return array_map(function ($line) {
			$sqlSort = new SqlSort();
			if (StringHelper::startsWith($line, false, "-", "+")) {
				$sqlSort->setKey(substr($line, 1));
				if ($line{0} === "-") {
					$sqlSort->setType(SqlSort::TYPE_DESC);
				}
			} else {
				$sqlSort->setKey($line)->setType(SqlSort::TYPE_DEFAULT);
			}
			return $sqlSort->setKey(self::prop2field($sqlSort->getKey()));
		}, $fields);
	}

	/**
	 * 从数据库中查找一条数据
	 * @param string $where where语句
	 * @return BaseModel
	 * @throws SqlException
	 * @throws ModelException
	 */
	public static function find(string $where)
	{
		$class = get_called_class();
		if ($class === BaseModel::class) {
			throw new ModelException("不要直接调用BaseModel::find, 请使用具体模型类::find");
		}
		$database = self::_database();
		$table = self::_table();

		$row = $database->row($table, $where);

		return self::row2model($row);
	}

	/**
	 * 执行分页查询
	 * @param int $pageIndex 页码
	 * @param int $pageSize 页大小
	 * @param string|null $search 要搜索的内容
	 * @param Sql|string|null $where where语句
	 * @param SqlSort[]|null $sort 排序
	 * @return Pager
	 * @throws SqlException
	 */
	public static function pager(
		int $pageIndex,
		int $pageSize = 20,
		string $search = null,
		$where = null,
		array $sort = null): Pager
	{
		$fields = self::_fields();
		if ($sort) {
			$sort = array_map(function ($sort) {
				if ($sort instanceof SqlSort) {
					$sort->setKey(self::prop2field($sort->getKey()));
				}
				return $sort;
			}, $sort);
		}
		$pager = self::_database()->pager(
			self::_table(),
			$pageIndex,
			$pageSize,
			$search,
			$where,
			$sort,
			self::_searchableFields(),
			array_filter(array_keys($fields), function ($item) {
				return !in_array($item, self::_bigDataFields());
			})
		);
		return new Pager(
			$pager->getTotal(),
			$pager->getCurrent(),
			$pager->getSize(),
			array_map('self::row2model', $pager->getData())
		);
	}

	private static function _searchableFields()
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	private static function _bigDataFields()
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	/**
	 * @param string|null $where
	 * @return float
	 * @throws SqlException
	 */
	public static function count(string $where = null): float
	{
		return self::_database()->count(self::_table(), $where);
	}

	/**
	 * @param string $column
	 * @param string|null $where
	 * @return float
	 * @throws SqlException
	 */
	public static function max(string $column, string $where = null): float
	{
		return self::_database()->max(self::_table(), $column, $where);
	}

	/**
	 * @param string $column
	 * @param string|null $where
	 * @return float
	 * @throws SqlException
	 */
	public static function min(string $column, string $where = null): float
	{
		return self::_database()->min(self::_table(), $column, $where);
	}

	/**
	 * @param string $column
	 * @param string|null $where
	 * @return float
	 * @throws SqlException
	 */
	public static function avg(string $column, string $where = null): float
	{
		return self::_database()->avg(self::_table(), $column, $where);
	}

	public static function database(): BaseDb
	{
		return Mvc::getDatabase();
	}

	/**
	 * 表名, 不重写该方法默认使用表前缀加当前模型类名做为表名
	 * 若重写该方法, 则调用时不会自动添加表前缀
	 * 比如:
	 * UserModel: {$tablePrefix}user
	 * ArticleCategoryModel: {$tablePrefix}article_category
	 * ViewArticleTagModel: {$tablePrefix}view_article_tag
	 * @return string
	 */
	public static function table(): string
	{
		$name = CollectionHelper::last(explode('\\', get_called_class()), '');
		$name = str_replace('Model', '', $name);
		return Mvc::getTablePrefix() . StringHelper::hump2underLine($name);
	}

	/**
	 * 大数据字段名, 大数据字段在列表查找时将不在结果中显示值
	 * @return array
	 */
	public static function bigDataFields(): array
	{
		return [];
	}

	/**
	 * 返回可被搜索的字段名
	 * @return array
	 */
	public static function searchableFields(): array
	{
		return [];
	}

	/**
	 * 返回需要对值执行trim的字段名
	 * @return array
	 */
	public static function needTrimFields(): array
	{
		return [];
	}

	/**
	 * 当前模型是否为只读模型
	 * @return bool
	 */
	public static function readOnly(): bool
	{
		return false;
	}

	/**
	 * 更新当前记录
	 * @param array|null $fields
	 * @return bool
	 * @throws SqlException
	 * @throws ModelException
	 */
	public function update(array $fields = null): bool
	{
		if (self::_readOnly()) {
			throw new ModelException("当前模型为只读模型");
		}
		if (!$this->isExist()) {
			throw new ModelException("当前记录不在数据库中, 无法更新, 请使用insert方法");
		}
		$kvs = [];
		$fs = self::_fields();
		$pk = self::_primaryKeyFields();
		if (is_array($fields) && count($fields) > 0) {
			foreach ($fields as $field) {
				if (!array_key_exists($field, $fs) || in_array($field, $pk)) continue;
				$kvs[$field] = $this->getValueFromField($field);
			}
		} else {
			foreach ($fs as $field => $property) {
				if (in_array($field, $pk)) continue;
				$kvs[$field] = $this->getValueFromField($field);
			}
		}
		$db = self::_database();
		$params = [];
		$where = join(" AND ", array_map(function ($item) use (&$params) {
			$value = $this->getValueFromField($item);
			if (is_bool($value)) {
				$value = $value ? 1 : 0;
			}
			$params[$item] = $value;
			return "`{$item}` = :{$item}";
		}, $pk));
		return $db->update(self::_table(), $kvs, new Sql($where, $params)) > 0;
	}

	private static function _readOnly(): bool
	{
		$class = get_called_class();
		$method = str_replace('_', '', __FUNCTION__);
		return $class::$method();
	}

	/**
	 * @return bool
	 */
	public function isExist(): bool
	{
		return $this->exist;
	}

	/**
	 * 获取数据库字段对应的值
	 * @param string $field
	 * @return null|mixed
	 */
	public function getValueFromField(string $field)
	{
		$fields = self::_fields();
		try {
			return $this->getValueFromProperty($fields[$field]);
		} catch (Throwable $e) {
			Mvc::getLogger()->error("设置字段值异常", $e->getMessage(), $e);
			return null;
		}
	}

	public function getValueFromProperty(string $property)
	{
		$class = get_called_class();
		try {
			$property = ObjectHelper::getProperty($class, $property);
			$property->setAccessible(true);
			return $property->getValue($this);
		} catch (ReflectionException $e) {
			Mvc::getLogger()->error("设置字段值异常", $e->getMessage(), $e);
			return null;
		}
	}

	/**
	 * 将当前数据插入到数据库
	 * @return bool
	 * @throws SqlException
	 * @throws ModelException
	 */
	public function insert(): bool
	{
		if (self::_readOnly()) {
			throw new ModelException("当前模型为只读模型");
		}
		if ($this->isExist()) {
			throw new ModelException("当前记录已在数据库中, 无法插入, 请使用update方法");
		}
		$kvs = [];
		$db = self::_database();
		$fs = self::_fields();
		foreach ($fs as $field => $property) {
			$kvs[$field] = $this->getValueFromField($field);
		}
		return $db->insert(self::_table(), $kvs) > 0;
	}

	public function toJsonString(): string
	{
		return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @return array
	 */
	public function toMap(): array
	{
		$map = [];
		try {
			$fields = self::_fields();
			$class = get_called_class();
			foreach ($fields as $field => $propertyName) {
				$property = ObjectHelper::getProperty($class, $propertyName);
				if (!$property) continue;
				$property->setAccessible(true);
				$value = $property->getValue($this);
				$map[$propertyName] = is_numeric($value) ? +$value : $value;
			}
		} catch (Throwable $e) {
			Mvc::getLogger()->error("模型序列化异常", $e->getMessage(), $e);
		}
		return $map;
	}

	private static function setFieldRawValue()
	{

	}

	public function jsonSerialize()
	{
		return $this->toMap();
	}

	/**
	 * 设置值到数据库对应字段
	 * @param string $field 数据表里的字段名
	 * @param mixed $value 值
	 * @return $this
	 * @throws ReflectionException
	 */
	public function setValueToField(string $field, $value)
	{
		$fields = self::_fields();
		$this->setValueToProperty($fields[$field], $value);
		return $this;
	}

	/**
	 * 设置值到模型属性名
	 * @param string $property 模型对象对应的属性名
	 * @param mixed $value 值
	 * @return $this
	 * @throws ReflectionException
	 */
	public function setValueToProperty(string $property, $value)
	{
		$class = get_called_class();
		$property = ObjectHelper::getProperty($class, $property);
		$property->setAccessible(true);
		$property->setValue($this, $value);
		return $this;
	}

	private static function _checkCalledClass(string $method)
	{
		if (get_called_class() === BaseModel::class) {
			throw new RuntimeException("请不要直接在BaseModel上调方法$method");
		}
	}
}
