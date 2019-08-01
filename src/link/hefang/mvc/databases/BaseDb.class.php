<?php

namespace link\hefang\mvc\databases;


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\mvc\entities\Pager;
use link\hefang\mvc\exceptions\SqlException;

abstract class BaseDb
{
    private $host = '';
    private $username = '';
    private $password = '';
    private $database = '';
    private $port = -1;
    private $charset = 'utf8';


    /**
     * BaseDb constructor.
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $charset
     * @param int $port
     */
    public function __construct(
        string $host,
        string $username = null,
        string $password = null,
        string $database = null,
        string $charset = null,
        int $port = -1
    )
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->charset = ObjectHelper::nullOrDefault($charset, $this->charset);
    }

    public abstract function errorInfo();

    public abstract function errorCode();

    /**
     * 获取到目前为止已执行的sql语句
     * @return array
     */
    public static abstract function getExecutedSqls(): array;

    /**
     * 插入记录
     * @param string $table
     * @param array $data
     * @return float
     * @throws SqlException
     */
    public abstract function insert(string $table, array $data): float;

    /**
     * 删除记录
     * @param string $table
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function delete(string $table, $where = null): float;

    /**
     * 更新记录
     * @param string $table
     * @param array $data
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function update(string $table, array $data, $where = null): float;

    /**
     * 分页查询
     * @param string $table
     * @param int $pageIndex
     * @param int $pageSize
     * @param string|null $search
     * @param Sql|string|null $where
     * @param array|null $sort
     * @param array|null $field2search
     * @param array|null $field2show
     * @return Pager
     * @throws SqlException
     */
    public abstract function pager(
        string $table,
        int $pageIndex,
        int $pageSize,
        string $search = null,
        $where = null,
        array $sort = null,
        array $field2search = null,
        array $field2show = null
    ): Pager;

    /**
     * 获取一条记录
     * @param string $table
     * @param Sql|string|null $where
     * @param array|null $fields
     * @return mixed
     * @throws SqlException
     */
    public abstract function row(string $table, $where = null, array $fields = null);

    /**
     * 查询某个字段
     * @param string $table
     * @param string $column
     * @param Sql|string|null $where
     * @return mixed|null
     * @throws SqlException
     */
    public function single(string $table, string $column, $where = null)
    {
        $row = $this->row($table, $where, [$column]);
        return CollectionHelper::getOrDefault($row ?: [], $column, null);
    }

    /**
     * 计算记录数
     * @param string $table
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function count(string $table, $where = null): float;

    /**
     * 求和
     * @param string $table
     * @param string $column
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function sum(string $table, string $column, $where = null): float;

    /**
     * 求平均值
     * @param string $table
     * @param string $column
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function avg(string $table, string $column, $where = null): float;

    /**
     * 求最大值
     * @param string $table
     * @param string $column
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function max(string $table, string $column, $where = null): float;

    /**
     * 求最小值
     * @param string $table
     * @param string $column
     * @param Sql|string|null $where
     * @return float
     * @throws SqlException
     */
    public abstract function min(string $table, string $column, $where = null): float;

    /**
     * 执行一个事务
     * @param Sql $sqls [optional]
     * @return int 受影响的行数
     * @throws SqlException
     */
    public abstract function transaction($sqls): int;

    /**
     * 执行insert,delete,update等更新语句
     * @param Sql $sql
     * @return float
     * @throws SqlException
     */
    public abstract function executeUpdate(Sql $sql): float;

    /**
     * 执行sql语句
     * @param Sql $sql
     * @return mixed
     * @throws SqlException
     */
    public abstract function executeQuery(Sql $sql);

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return BaseDb
     */
    public function setHost(string $host): BaseDb
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return BaseDb
     */
    public function setUsername(string $username): BaseDb
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return BaseDb
     */
    public function setPassword(string $password): BaseDb
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param string $database
     * @return BaseDb
     */
    public function setDatabase(string $database): BaseDb
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     * @return BaseDb
     */
    public function setPort(string $port): BaseDb
    {
        $this->port = $port;
        return $this;
    }
}