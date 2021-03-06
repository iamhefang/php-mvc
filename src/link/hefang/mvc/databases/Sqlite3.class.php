<?php

namespace link\hefang\mvc\databases;

use link\hefang\mvc\entities\Pager;
use link\hefang\mvc\exceptions\SqlException;
use link\hefang\mvc\Mvc;
use PDO;

// todo: Sqlite3还未完成
class Sqlite3 extends BaseDb
{

	private $errorCode;
	private $errorInfo;
	private static $sqls = [];
	/**
	 * @var PDO
	 */
	private $pdo;

	public function __construct(string $filename)
	{
		parent::__construct($filename, null, null, null, null, null);
		$this->pdo = new PDO("sqlite:" . $filename);
	}

	public function errorInfo()
	{
		return $this->errorInfo;
	}

	public function errorCode()
	{
		return $this->errorCode;
	}

	/**
	 * 获取到目前为止已执行的sql语句
	 * @return array
	 */
	public static function getExecutedSqls(): array
	{
		return self::$sqls;
	}

	/**
	 * 插入记录
	 * @param string $table
	 * @param array $data
	 * @return float
	 * @throws SqlException
	 */
	public function insert(string $table, array $data): float
	{
		$columns = join(", ", array_map("self::addQuotes", array_keys($data)));
		$values = ':' . join(', :', array_keys($data));
		$sql = new Sql("INSERT INTO \"$table\"($columns) VALUES ($values);", $data);
		return $this->executeUpdate($sql);
	}

	public function delete(string $table, $where = null): float
	{
		$params = [];
		$w = $where;
		if ($where instanceof Sql) {
			$params = $where->getParams();
			$w = $where->getSql();
		}
		$sql = new Sql("DELETE FROM \"$table\"" . self::makeWhere($w) . ";", $params);
		return $this->executeUpdate($sql);
	}

	public function update(string $table, array $data, $where = null): float
	{
		$params = [];
		if ($where instanceof Sql) {
			$params = $where->getParams();
			$w = $where->getSql();
		} else {
			$w = $where;
		}
		$params = array_merge($params, $data);
		$cols = [];
		foreach (array_keys($data) as $key) {
			$cols[] = self::addQuotes($key) . " = :$key";
		}
		$columns = join(', ', $cols);
		$sql = new Sql("UPDATE \"$table\" SET $columns" . self::makeWhere($w) . ';', $params);
		return $this->executeUpdate($sql->setParams($params));
	}

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
	public function pager(string $table, int $pageIndex, int $pageSize, string $search = null, $where = null, array $sort = null, array $field2search = null, array $field2show = null): Pager
	{
		// TODO: Implement pager() method.
	}

	/**
	 * 获取一条记录
	 * @param string $table
	 * @param Sql|string|null $where
	 * @param array|null $fields
	 * @return mixed
	 * @throws SqlException
	 */
	public function row(string $table, $where = null, array $fields = null)
	{
		// TODO: Implement row() method.
	}

	public function count(string $table, $where = null): float
	{
		return $this->single($table, strtoupper(__FUNCTION__) . "(*)", $where);
	}

	public function sum(string $table, string $column, $where = null): float
	{
		return $this->single($table, strtoupper(__FUNCTION__) . "($column)", $where);
	}

	public function avg(string $table, string $column, $where = null): float
	{
		return $this->single($table, strtoupper(__FUNCTION__) . "($column)", $where);
	}

	public function max(string $table, string $column, $where = null): float
	{
		return $this->single($table, strtoupper(__FUNCTION__) . "($column)", $where);
	}

	public function min(string $table, string $column, $where = null): float
	{
		return $this->single($table, strtoupper(__FUNCTION__) . "($column)", $where);
	}

	/**
	 * 执行一个事务
	 * @param Sql $sqls [optional]
	 * @return int 受影响的行数
	 * @throws SqlException
	 */
	public function transaction($sqls): int
	{
		$this->pdo->beginTransaction();
		is_array($sqls) or $sqls = func_get_args();
		$count = 0;
		foreach ($sqls as $sql) {
			$params = $sql->getParams();
			$stmt = $this->pdo->prepare($sql->getSql());
			foreach ($params as $key => $value) {
				if (is_bool($value)) {
					$value = $value ? 1 : 0;
				}
				$stmt->bindValue(':' . $key, $value);
			}
			if ($stmt->execute()) {
				if ($stmt->errorCode() !== '00000') {
					$this->errorCode = $stmt->errorCode();
					$this->errorInfo = $stmt->errorInfo();
					$this->pdo->rollBack();
					break;
				} else {
					$count += $stmt->rowCount();
				}
				$stmt->closeCursor();
			}
		}
		return $this->pdo->commit() ? $count : 0;
	}

	/**
	 * 执行insert,delete,update等更新语句
	 * @param Sql $sql
	 * @return float
	 * @throws SqlException
	 */
	public function executeUpdate(Sql $sql): float
	{
		self::_log($sql);

		$stmt = $this->pdo->prepare($sql->getSql());
		foreach ($sql->getParams() as $key => $value) {
			if (is_bool($value)) {
				$value = $value ? 1 : 0;
			}
			$stmt->bindValue(':' . $key, $value);
		}
		$res = $stmt->execute();
		$res and $res = $stmt->rowCount();
		if ($stmt->errorCode() !== '00000') {
			$this->errorCode = $stmt->errorCode();
			$this->errorInfo = $stmt->errorInfo();
			$stmt->closeCursor();
			throw SqlException::newInstance($this->errorInfo, $sql);
		}
		$stmt->closeCursor();

		return $res;
	}

	/**
	 * 执行sql语句
	 * @param Sql $sql
	 * @return mixed
	 * @throws SqlException
	 */
	public function executeQuery(Sql $sql)
	{
		self::_log($sql);

		$stmt = $this->pdo->prepare($sql->getSql());
		foreach ($sql->getParams() as $key => $value) {
			if (is_bool($value)) {
				$value = $value ? 1 : 0;
			}
			$stmt->bindValue(':' . $key, $value);
		}
		$res = $stmt->execute();
		if ($stmt->errorCode() !== '00000') {
			$this->errorCode = $stmt->errorCode();
			$this->errorInfo = $stmt->errorInfo();
			$stmt->closeCursor();
			throw SqlException::newInstance($this->errorInfo, $sql);
		}

		$res and $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return $res ? $res : [];
	}

	private static function _log(Sql $sql)
	{
		self::$sqls[] = $sql;
		$params = '';
		foreach ($sql->getParams() as $key => $value) {
			$type = gettype($value);
			if (is_bool($value)) {
				$value = $value ? 'true' : 'false';
			}
			$params .= "\n\r\r{$key}({$type}): {$value}";
		}
		$params or $params = '无';
		Mvc::getLogger()->debug("执行SQL语句", "SQL语句: {$sql->getSql()}\n参数: {$params}");
	}

	public static function addQuotes(string $item): string
	{
		return in_array(strtoupper($item), self::KEYWORDS) ? '"' . $item . '"' : $item;
	}

	const KEYWORDS = [
		'ACCESSIBLE', 'ACCOUNT', 'ACTION', 'ADD', 'AFTER', 'AGAINST', 'AGGREGATE', 'ALGORITHM', 'ALL', 'ALTER', 'ALWAYS', 'ANALYSE', 'ANALYZE', 'AND', 'ANY', 'AS', 'ASC', 'ASCII', 'ASENSITIVE', 'AT', 'AUTOEXTEND_SIZE', 'AUTO_INCREMENT', 'AVG', 'AVG_ROW_LENGTH', 'BACKUP', 'BEFORE', 'BEGIN', 'BETWEEN', 'BIGINT', 'BINARY', 'BINLOG', 'BIT', 'BLOB', 'BLOCK', 'BOOL', 'BOOLEAN', 'BOTH', 'BTREE', 'BY', 'BYTE', 'CACHE', 'CALL', 'CASCADE', 'CASCADED', 'CASE', 'CATALOG_NAME', 'CHAIN', 'CHANGE', 'CHANGED', 'CHANNEL', 'CHAR', 'CHARACTER', 'CHARSET', 'CHECK', 'CHECKSUM', 'CIPHER', 'CLASS_ORIGIN', 'CLIENT', 'CLOSE', 'COALESCE', 'CODE', 'COLLATE', 'COLLATION', 'COLUMN', 'COLUMNS', 'COLUMN_FORMAT', 'COLUMN_NAME', 'COMMENT', 'COMMIT', 'COMMITTED', 'COMPACT', 'COMPLETION', 'COMPRESSED', 'COMPRESSION', 'CONCURRENT', 'CONDITION', 'CONNECTION', 'CONSISTENT', 'CONSTRAINT', 'CONSTRAINT_CATALOG', 'CONSTRAINT_NAME', 'CONSTRAINT_SCHEMA', 'CONTAINS', 'CONTEXT', 'CONTINUE', 'CONVERT', 'CPU', 'CREATE', 'CROSS', 'CUBE', 'CURRENT', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'CURSOR_NAME', 'DATA', 'DATABASE', 'DATABASES', 'DATAFILE', 'DATE', 'DATETIME', 'DAY', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEALLOCATE', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DEFAULT_AUTH', 'DEFINER', 'DELAYED', 'DELAY_KEY_WRITE', 'DELETE', 'DESC', 'DESCRIBE', 'DES_KEY_FILE', 'DETERMINISTIC', 'DIAGNOSTICS', 'DIRECTORY', 'DISABLE', 'DISCARD', 'DISK', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DO', 'DOUBLE', 'DROP', 'DUAL', 'DUMPFILE', 'DUPLICATE', 'DYNAMIC', 'EACH', 'ELSE', 'ELSEIF', 'ENABLE', 'ENCLOSED', 'ENCRYPTION', 'END', 'ENDS', 'ENGINE', 'ENGINES', 'ENUM', 'ERROR', 'ERRORS', 'ESCAPE', 'ESCAPED', 'EVENT', 'EVENTS', 'EVERY', 'EXCHANGE', 'EXECUTE', 'EXISTS', 'EXIT', 'EXPANSION', 'EXPIRE', 'EXPLAIN', 'EXPORT', 'EXTENDED', 'EXTENT_SIZE', 'FALSE', 'FAST', 'FAULTS', 'FETCH', 'FIELDS', 'FILE', 'FILE_BLOCK_SIZE', 'FILTER', 'FIRST', 'FIXED', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FLUSH', 'FOLLOWS', 'FOR', 'FORCE', 'FOREIGN', 'FORMAT', 'FOUND', 'FROM', 'FULL', 'FULLTEXT', 'FUNCTION', 'GENERAL', 'GENERATED', 'GEOMETRY', 'GEOMETRYCOLLECTION', 'GET', 'GET_FORMAT', 'GLOBAL', 'GRANT', 'GRANTS', 'GROUP', 'GROUP_REPLICATION', 'HANDLER', 'HASH', 'HAVING', 'HELP', 'HIGH_PRIORITY', 'HOST', 'HOSTS', 'HOUR', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IDENTIFIED', 'IF', 'IGNORE', 'IGNORE_SERVER_IDS', 'IMPORT', 'IN', 'INDEX', 'INDEXES', 'INFILE', 'INITIAL_SIZE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INSERT_METHOD', 'INSTALL', 'INSTANCE', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'INVOKER', 'IO', 'IO_AFTER_GTIDS', 'IO_BEFORE_GTIDS', 'IO_THREAD', 'IPC', 'IS', 'ISOLATION', 'ISSUER', 'ITERATE', 'JOIN', 'JSON', 'KEY', 'KEYS', 'KEY_BLOCK_SIZE', 'KILL', 'LANGUAGE', 'LAST', 'LEADING', 'LEAVE', 'LEAVES', 'LEFT', 'LESS', 'LEVEL', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LINESTRING', 'LIST', 'LOAD', 'LOCAL', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LOCKS', 'LOGFILE', 'LOGS', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MASTER', 'MASTER_AUTO_POSITION', 'MASTER_BIND', 'MASTER_CONNECT_RETRY', 'MASTER_DELAY', 'MASTER_HEARTBEAT_PERIOD', 'MASTER_HOST', 'MASTER_LOG_FILE', 'MASTER_LOG_POS', 'MASTER_PASSWORD', 'MASTER_PORT', 'MASTER_RETRY_COUNT', 'MASTER_SERVER_ID', 'MASTER_SSL', 'MASTER_SSL_CA', 'MASTER_SSL_CAPATH', 'MASTER_SSL_CERT', 'MASTER_SSL_CIPHER', 'MASTER_SSL_CRL', 'MASTER_SSL_CRLPATH', 'MASTER_SSL_KEY', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MASTER_TLS_VERSION', 'MASTER_USER', 'MATCH', 'MAXVALUE', 'MAX_CONNECTIONS_PER_HOUR', 'MAX_QUERIES_PER_HOUR', 'MAX_ROWS', 'MAX_SIZE', 'MAX_STATEMENT_TIME', 'MAX_UPDATES_PER_HOUR', 'MAX_USER_CONNECTIONS', 'MEDIUM', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MEMORY', 'MERGE', 'MESSAGE_TEXT', 'MICROSECOND', 'MIDDLEINT', 'MIGRATE', 'MINUTE', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MIN_ROWS', 'MOD', 'MODE', 'MODIFIES', 'MODIFY', 'MONTH', 'MULTILINESTRING', 'MULTIPOINT', 'MULTIPOLYGON', 'MUTEX', 'MYSQL_ERRNO', 'NAME', 'NAMES', 'NATIONAL', 'NATURAL', 'NCHAR', 'NDB', 'NDBCLUSTER', 'NEVER', 'NEW', 'NEXT', 'NO', 'NODEGROUP', 'NONBLOCKING', 'NONE', 'NOT', 'NO_WAIT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMBER', 'NUMERIC', 'NVARCHAR', 'OFFSET', 'OLD_PASSWORD', 'ON', 'ONE', 'ONLY', 'OPEN', 'OPTIMIZE', 'OPTIMIZER_COSTS', 'OPTION', 'OPTIONALLY', 'OPTIONS', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'OWNER', 'PACK_KEYS', 'PAGE', 'PARSER', 'PARSE_GCOL_EXPR', 'PARTIAL', 'PARTITION', 'PARTITIONING', 'PARTITIONS', 'PASSWORD', 'PHASE', 'PLUGIN', 'PLUGINS', 'PLUGIN_DIR', 'POINT', 'POLYGON', 'PORT', 'PRECEDES', 'PRECISION', 'PREPARE', 'PRESERVE', 'PREV', 'PRIMARY', 'PRIVILEGES', 'PROCEDURE', 'PROCESSLIST', 'PROFILE', 'PROFILES', 'PROXY', 'PURGE', 'QUARTER', 'QUERY', 'QUICK', 'RANGE', 'READ', 'READS', 'READ_ONLY', 'READ_WRITE', 'REAL', 'REBUILD', 'RECOVER', 'REDOFILE', 'REDO_BUFFER_SIZE', 'REDUNDANT', 'REFERENCES', 'REGEXP', 'RELAY', 'RELAYLOG', 'RELAY_LOG_FILE', 'RELAY_LOG_POS', 'RELAY_THREAD', 'RELEASE', 'RELOAD', 'REMOVE', 'RENAME', 'REORGANIZE', 'REPAIR', 'REPEAT', 'REPEATABLE', 'REPLACE', 'REPLICATE_DO_DB', 'REPLICATE_DO_TABLE', 'REPLICATE_IGNORE_DB', 'REPLICATE_IGNORE_TABLE', 'REPLICATE_REWRITE_DB', 'REPLICATE_WILD_DO_TABLE', 'REPLICATE_WILD_IGNORE_TABLE', 'REPLICATION', 'REQUIRE', 'RESET', 'RESIGNAL', 'RESTORE', 'RESTRICT', 'RESUME', 'RETURN', 'RETURNED_SQLSTATE', 'RETURNS', 'REVERSE', 'REVOKE', 'RIGHT', 'RLIKE', 'ROLLBACK', 'ROLLUP', 'ROTATE', 'ROUTINE', 'ROW', 'ROWS', 'ROW_COUNT', 'ROW_FORMAT', 'RTREE', 'SAVEPOINT', 'SCHEDULE', 'SCHEMA', 'SCHEMAS', 'SCHEMA_NAME', 'SECOND', 'SECOND_MICROSECOND', 'SECURITY', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SERIAL', 'SERIALIZABLE', 'SERVER', 'SESSION', 'SET', 'SHARE', 'SHOW', 'SHUTDOWN', 'SIGNAL', 'SIGNED', 'SIMPLE', 'SLAVE', 'SLOW', 'SMALLINT', 'SNAPSHOT', 'SOCKET', 'SOME', 'SONAME', 'SOUNDS', 'SOURCE', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQL_AFTER_GTIDS', 'SQL_AFTER_MTS_GAPS', 'SQL_BEFORE_GTIDS', 'SQL_BIG_RESULT', 'SQL_BUFFER_RESULT', 'SQL_CACHE', 'SQL_CALC_FOUND_ROWS', 'SQL_NO_CACHE', 'SQL_SMALL_RESULT', 'SQL_THREAD', 'SQL_TSI_DAY', 'SQL_TSI_HOUR', 'SQL_TSI_MINUTE', 'SQL_TSI_MONTH', 'SQL_TSI_QUARTER', 'SQL_TSI_SECOND', 'SQL_TSI_WEEK', 'SQL_TSI_YEAR', 'SSL', 'STACKED', 'START', 'STARTING', 'STARTS', 'STATS_AUTO_RECALC', 'STATS_PERSISTENT', 'STATS_SAMPLE_PAGES', 'STATUS', 'STOP', 'STORAGE', 'STORED', 'STRAIGHT_JOIN', 'STRING', 'SUBCLASS_ORIGIN', 'SUBJECT', 'SUBPARTITION', 'SUBPARTITIONS', 'SUPER', 'SUSPEND', 'SWAPS', 'SWITCHES', 'TABLE', 'TABLES', 'TABLESPACE', 'TABLE_CHECKSUM', 'TABLE_NAME', 'TEMPORARY', 'TEMPTABLE', 'TERMINATED', 'TEXT', 'THAN', 'THEN', 'TIME', 'TIMESTAMP', 'TIMESTAMPADD', 'TIMESTAMPDIFF', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRANSACTION', 'TRIGGER', 'TRIGGERS', 'TRUE', 'TRUNCATE', 'TYPE', 'TYPES', 'UNCOMMITTED', 'UNDEFINED', 'UNDO', 'UNDOFILE', 'UNDO_BUFFER_SIZE', 'UNICODE', 'UNINSTALL', 'UNION', 'UNIQUE', 'UNKNOWN', 'UNLOCK', 'UNSIGNED', 'UNTIL', 'UPDATE', 'UPGRADE', 'USAGE', 'USE', 'USER', 'USER_RESOURCES', 'USE_FRM', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALIDATION', 'VALUE', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARIABLES', 'VARYING', 'VIEW', 'VIRTUAL', 'WAIT', 'WARNINGS', 'WEEK', 'WEIGHT_STRING', 'WHEN', 'WHERE', 'WHILE', 'WITH', 'WITHOUT', 'WORK', 'WRAPPER', 'WRITE', 'X509', 'XA', 'XID', 'XML', 'XOR', 'YEAR', 'YEAR_MONTH', 'ZEROFILL', 'ACCOUNT', 'ALWAYS', 'CHANNEL', 'COMPRESSION', 'ENCRYPTION', 'FILE_BLOCK_SIZE', 'FILTER', 'FOLLOWS', 'GENERATED', 'GROUP_REPLICATION', 'INSTANCE', 'JSON', 'MASTER_TLS_VERSION', 'NEVER', 'OPTIMIZER_COSTS', 'PARSE_GCOL_EXPR', 'PRECEDES', 'REPLICATE_DO_DB', 'REPLICATE_DO_TABLE', 'REPLICATE_IGNORE_DB', 'REPLICATE_IGNORE_TABLE', 'REPLICATE_REWRITE_DB', 'REPLICATE_WILD_DO_TABLE', 'REPLICATE_WILD_IGNORE_TABLE', 'ROTATE', 'STACKED', 'STORED', 'VALIDATION', 'VIRTUAL', 'WITHOUT', 'XID', 'MIN', 'MAX', 'AVG', 'SUM', 'COUNT'
	];
}
