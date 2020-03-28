<?php


namespace link\hefang\mvc\models;


use JsonSerializable;
use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\ParseHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\databases\BaseDb;
use link\hefang\mvc\databases\Sql;
use link\hefang\mvc\databases\SqlSort;
use link\hefang\mvc\entities\Pager;
use link\hefang\mvc\exceptions\ModelException;
use link\hefang\mvc\exceptions\SqlException;
use link\hefang\mvc\interfaces\IModel2;
use link\hefang\mvc\Mvc;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

abstract class BaseModel2 implements IMapObject, IJsonObject, JsonSerializable, IModel2
{
	private $exist = false;

	/**
	 * @param string $id
	 * @return BaseModel2
	 * @throws ModelException
	 * @throws SqlException
	 */
	public static function get(string $id): BaseModel2
	{
		$primaryKeys = self::_primaryKeys();
		if (empty($primaryKeys)) {
			throw new ModelException("该模型没有设置主键");
		}
		if (count($primaryKeys) > 1) {
			throw new ModelException("多主键模型请使用find");
		}
		return self::find("`$primaryKeys[0]`='$id'");
	}

	/**
	 * @return string[]
	 */
	private static function _primaryKeys(): array
	{
		return array_map(function (ModelField $field) {
			return $field->getField();
		}, array_filter(self::_fields(), function (ModelField $field) {
			return $field->isPrimaryKey();
		}));
	}

	/**
	 * @param string $where
	 * @return BaseModel2
	 * @throws SqlException
	 */
	public static function find(string $where): BaseModel2
	{
		$row = self::_database()->row(self::_table(), $where);
		return self::_row2model($row);
	}

	/**
	 * @param int $pageIndex
	 * @param int $pageSize
	 * @param string|null|Sql $where
	 * @param SqlSort[]|null $sort
	 * @return Pager
	 * @throws SqlException
	 */
	public static function pager(int $pageIndex = 1, int $pageSize = 10, $where = null, array $sort = null): Pager
	{
		$fields = self::_fields();
		$db = self::database();
		$fields2show = [];

		foreach ($fields as $field) {
			if (!$field->isHideInResult()) {
				$fields2show[] = $field->getField();
			}
		}
		if ($sort) {
			foreach ($sort as &$sortItem) {
				$sortItem->setKey(CollectionHelper::getOrDefault(self::propFieldMap(), $sortItem->getKey(), $sortItem->getKey()));
			}
		}
		$pager = $db->pager(self::table(), $pageIndex, $pageSize, null, $where, $sort, null, $fields2show);

		return new Pager($pager->getTotal(), $pager->getCurrent(), $pager->getSize(), array_map('self::_row2model', $pager->getData()));
	}

	/**
	 * 返回模型对应的数据库连接
	 * @return BaseDb
	 */
	public static function database(): BaseDb
	{
		return Mvc::getDatabase();
	}

	/**
	 * 返回模型对应的数据库表
	 * @return string
	 */
	public static function table(): string
	{
		$class = self::_checkClass(__FUNCTION__);
		$class = CollectionHelper::last(explode("\\", $class));
		$class = StringHelper::hump2underLine(str_replace("Model", "", $class));
		return $class;
	}

	/**
	 * @return array
	 */
	public static function fieldObjMap(): array
	{
		$map = [];
		foreach (self::_fields() as $field) {
			$map[$field->getField()] = $field;
		}
		return $map;
	}

	/**
	 *
	 * @param string|null $query
	 * @return string
	 */
	public static function query2sql($query): string
	{
		self::_checkClass(__FUNCTION__);
		if (StringHelper::isNullOrBlank($query)) return "";

		$query = preg_replace_callback("/([-a-z0-9_]+)(=|~=|!=|>|<|>=|<=)([^!%&|~=)(]+)/i", function (array $match) {
			$field = CollectionHelper::getOrDefault(self::propObjMap(), $match[1]);
			$value = $match[3];

			$fieldName = $field ? $field->getField() : $match[1];

			if ($match[2] === "~=") {
				return "(`{$fieldName}` LIKE '%{$value}' OR `{$fieldName}` LIKE '%{$fieldName}%' OR `{$fieldName}` LIKE '{$value}%')";
			}
			if ($field->getType() == ModelField::TYPE_BOOL) {
				$value = $value ? 1 : 0;
			} else if ($field->getType() == ModelField::TYPE_TEXT) {
				$value = "'{$value}'";
			}
			return "`{$fieldName}`{$match[2]}{$value}";
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
		self::_checkClass(__FUNCTION__);

		if (StringHelper::isNullOrBlank($sort)) return null;

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
			return $sqlSort->setKey(CollectionHelper::getOrDefault(self::propFieldMap(), $sqlSort->getKey(), $sqlSort->getKey()));
		}, $fields);
	}

	private static function _row2model(array $row): BaseModel2
	{
		$class = self::_checkClass(__FUNCTION__);
		$model = self::_newModel();
		$row or $row = [];
		try {
			foreach ($row as $field => $value) {
				$model->setValue2Field($value, $field);
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

	private static function _newModel(): BaseModel2
	{
		$class = self::_checkClass(__FUNCTION__);
		return new $class();
	}

	/**
	 * @param $value
	 * @param string $field
	 * @return BaseModel2
	 */
	public function setValue2Field($value, string $field): BaseModel2
	{
		return $this->setValue2Prop($value, CollectionHelper::getOrDefault(self::fieldPropMap(), $field, $field));
	}

	/**
	 * @param $value
	 * @param string $prop
	 * @return $this
	 */
	public function setValue2Prop($value, string $prop): BaseModel2
	{
		try {
			$ref = $this->_reflection();
			$property = $ref->getProperty($prop);
			$property->setAccessible(true);
			$property->setValue($this, $value);
		} catch (ReflectionException $e) {
			Mvc::getLogger()->error($e->getMessage(), "获取属性'$prop'值时失败", $e);
		}
		return $this;
	}

	/**
	 * 把当前模型数据插入数据库
	 * @return bool
	 * @throws ModelException
	 * @throws SqlException
	 */
	public function insert(): bool
	{
		if (self::readOnly()) {
			throw new ModelException("该模型为只读模型, 无法执行新增操作");
		}
		if ($this->isExist()) {
			throw new ModelException("该记录已存在, 无法执行新增操作");
		}
		$fields = self::_fields();
		$data = [];
		foreach ($fields as $field) {
			if ($field->isAutoIncrement()) continue;
			$data[$field->getField()] = $this->getValueFromField($field->getField());
		}
		return self::_database()->insert(self::_table(), $data) == 1;
	}

	private static function _checkClass(string $method): string
	{
		$class = get_called_class();
		if ($class === BaseModel2::class) {
			throw  new RuntimeException("请不要直接在BaseModel中调用方法{$method}");
		}
		return $class;
	}

	/**
	 * 当前模型是否为只读
	 * @return bool
	 */
	public static function readOnly(): bool
	{
		return false;
	}

	/**
	 * 当前模型对应的数据库记录是否存在
	 * @return bool
	 */
	public function isExist(): bool
	{
		return $this->exist;
	}

	public function getValueFromField(string $field)
	{
		return $this->getValueFromProp(CollectionHelper::getOrDefault(self::fieldPropMap(), $field, $field));
	}

	/**
	 * @param string $prop
	 * @return bool|string|int|float|null
	 */
	public function getValueFromProp(string $prop)
	{
		try {
			$ref = $this->_reflection();
			$property = $ref->getProperty($prop);
			$property->setAccessible(true);
			$value = $property->getValue($this);
			$field = self::propObjMap()[$prop];
			switch ($field->getType()) {
				case ModelField::TYPE_INT:
				case ModelField::TYPE_FLOAT:
					$value = +$value;
					break;
				case ModelField::TYPE_BOOL:
					$value = ParseHelper::parseBoolean($value);
					break;
			}
			if ($field->getTrim()) {
				$value = trim($value, $field->getTrim());
			}
			return $value;
		} catch (ReflectionException $e) {
			Mvc::getLogger()->error($e->getMessage(), "获取属性'$prop'值时失败", $e);
			return null;
		}
	}

	/**
	 * @return ReflectionClass
	 * @throws ReflectionException
	 */
	private function _reflection(): ReflectionClass
	{
		return new ReflectionClass(get_called_class());
	}

	public static function propObjMap(): array
	{
		$map = [];
		foreach (self::_fields() as $field) {
			$map[$field->getProp()] = $field;
		}
		return $map;
	}

	public static function fieldPropMap(): array
	{
		return array_flip(self::propFieldMap());
	}

	public static function propFieldMap(): array
	{
		$fields = self::_fields();
		$map = [];
		foreach ($fields as $field) {
			$map[$field->getProp()] = $field->getField();
		}
		return $map;
	}

	/**
	 * 更新当前模型对应的数据
	 * 注: 主键无法通过该方法更新
	 * @param string[]|null $fields2update 要更新的数据库属性名
	 * @return bool
	 * @throws ModelException
	 * @throws SqlException
	 */
	public function update(array $fields2update = null): bool
	{
		if (self::_readOnly()) {
			throw new ModelException("该模型为只读模型, 无法执行更新操作");
		}
		if (!$this->isExist()) {
			throw new ModelException("该记录不存在, 无法执行更新操作");
		}
		if ($fields2update) {
			$fields2update = array_map(function ($field) {
				return CollectionHelper::getOrDefault(self::propFieldMap(), $field, $field);
			}, $fields2update);
		} else {
			$fields2update = array_keys(self::fieldPropMap());
		}
		$fields = self::_fields();
		$data = [];
		$where = [];
		$params = [];
		foreach ($fields as $field) {
			$fieldName = $field->getField();
			$value = $this->getValueFromField($fieldName);
			if ($field->isAutoIncrement()) {
				continue;
			}
			if ($field->isPrimaryKey()) {
				if (!$value) {
					throw new ModelException("该记录主键为空, 无法更新");
				}
				$where[] = "`{$field->getField()}`=:{$field->getProp()}";
				$params[$field->getProp()] = $value;
			}
			if (in_array($field->getField(), $fields2update)) {
				$data[$fieldName] = $value;
			}
		}

		return self::_database()->update(self::_table(), $data, new Sql(join(" AND ", $where), $params)) == 1;
	}

	/**
	 * 删除当前记录
	 * @return bool
	 * @throws ModelException
	 * @throws SqlException
	 */
	public function delete(): bool
	{
		if (self::_readOnly()) {
			throw new ModelException("该模型为只读模型, 无法执行删除操作");
		}
		if ($this->isExist()) {
			throw new ModelException("该记录已存在, 无法执行删除操作");
		}
		$where = [];
		$params = [];
		foreach (self::_fields() as $field) {
			if (!$field->isPrimaryKey()) continue;
			$fieldName = $field->getField();
			$propName = $field->getProp();
			$value = $this->getValueFromField($fieldName);
			if (!$value) {
				throw new ModelException("该记录主键为空, 无法删除");
			}
			$where[] = "`$fieldName`=:$propName";
			$params[$propName] = $value;
		}
		return self::_database()->delete(self::_table(), new Sql(join(" AND ", $where), $params)) === 1;
	}

	public function toJsonString(): string
	{
		return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
	}

	public function toMap(): array
	{
		$fields = self::_fields();
		$map = [];

		foreach ($fields as $field) {
			if ($field->isHideInResult()) continue;
			$prop = $field->getProp();
			$map[$prop] = $this->getValueFromProp($prop);
		}

		return $map;
	}

	public function jsonSerialize()
	{
		return $this->toMap();
	}

	/**
	 * @return ModelField[]
	 */
	private static function _fields(): array
	{
		$class = self::_checkClass(__FUNCTION__);
		$method = substr(__FUNCTION__, 1);
		return $class::$method();
	}

	/**
	 * @return BaseDb
	 */
	private static function _database(): BaseDb
	{
		$class = self::_checkClass(__FUNCTION__);
		$method = substr(__FUNCTION__, 1);
		return $class::$method();
	}

	/**
	 * @return string
	 */
	private static function _table(): string
	{
		$class = self::_checkClass(__FUNCTION__);
		$method = substr(__FUNCTION__, 1);
		return $class::$method();
	}

	private static function _readOnly(): bool
	{
		$class = self::_checkClass(__FUNCTION__);
		$method = substr(__FUNCTION__, 1);
		return $class::$method();
	}
}
