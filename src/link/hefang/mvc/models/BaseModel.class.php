<?php

namespace link\hefang\mvc\models;


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\databases\BaseDb;
use link\hefang\mvc\databases\Mysql;
use link\hefang\mvc\databases\Sql;
use link\hefang\mvc\entities\Pager;
use link\hefang\mvc\exceptions\ModelException;
use link\hefang\mvc\exceptions\SqlException;
use link\hefang\mvc\interfaces\IModel;
use link\hefang\mvc\Mvc;


abstract class BaseModel implements IJsonObject, IMapObject, IModel, \JsonSerializable
{
    private $exist = false;

    /**
     * @return bool
     */
    public function isExist(): bool
    {
        return $this->exist;
    }

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
        if ($class === BaseModel::class || $class === BaseLoginModel::class) {
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
        if ($class === BaseModel::class || $class === BaseLoginModel::class) {
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
     * @param array|null $sort 排序
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
            $pager->getPageIndex(),
            $pager->getPageSize(),
            array_map('self::row2model', $pager->getData())
        );
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

    private static function _searchableFields()
    {
        $class = get_called_class();
        $method = str_replace('_', '', __FUNCTION__);
        return $class::$method();
    }

    private static function _needTrimFields()
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

    private static function _fields(): array
    {
        $class = get_called_class();
        $method = str_replace('_', '', __FUNCTION__);
        return $class::$method();
    }

    private static function _readOnly(): bool
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
            $ref = new \ReflectionClass($class);
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
        } catch (\ReflectionException $e) {
            Mvc::getLogger()->error("实例化模型'$class'异常", $e->getMessage(), $e);
            return $model;
        }
    }

    public static function database(): BaseDb
    {
        return Mvc::getDatabase();
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
        } catch (\Throwable $e) {
            Mvc::getLogger()->error("模型序列化异常", $e->getMessage(), $e);
        }
        return $map;
    }

    public function jsonSerialize()
    {
        return $this->toMap();
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
        } catch (\Throwable $e) {
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
        } catch (\ReflectionException $e) {
            Mvc::getLogger()->error("设置字段值异常", $e->getMessage(), $e);
            return null;
        }
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     * @throws \ReflectionException
     */
    public function setValueFromField(string $field, $value)
    {
        $fields = self::_fields();
        $this->setValueFromProperty($fields[$field], $value);
        return $this;
    }

    /**
     * @param string $property
     * @param $value
     * @return $this
     * @throws \ReflectionException
     */
    public function setValueFromProperty(string $property, $value)
    {
        $class = get_called_class();
        $property = ObjectHelper::getProperty($class, $property);
        $property->setAccessible(true);
        $property->setValue($this, $value);
        return $this;
    }

    /**
     * 表名, 不重写该方法默认使用当前类的模型
     * 比如:
     * UserModel: user
     * ArticleCategoryModel: article_category
     * ViewArticleTagModel: view_article_tag
     * @return string
     */
    public static function table(): string
    {
        $name = CollectionHelper::last(explode('\\', get_called_class()), '');
        $name = str_replace('Model', '', $name);
        return StringHelper::hump2underLine($name);
    }

    public static function bigDataFields(): array
    {
        return [];
    }

    public static function searchableFields(): array
    {
        return [];
    }

    public static function needTrimFields(): array
    {
        return [];
    }

    public static function readOnly(): bool
    {
        return false;
    }
}