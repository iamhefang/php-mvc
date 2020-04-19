<?php

namespace link\hefang\mvc\caches;
defined('PHP_MVC') or die("Access Refused");


use link\hefang\interfaces\ICache;
use link\hefang\mvc\Mvc;
use Redis;
use RuntimeException;

class RedisCache implements ICache
{
	private $namePrefix = "";
	/**
	 * @var Redis
	 */
	private $redis;

	/**
	 * ICache constructor.
	 * @param string $option 缓存选项, 缓存的实现类所需的选项不一定相同.
	 * 参数为 string 类型, 实现缓存类时可自行把参数做为 json 或 xml 解析
	 */
	public function __construct(string $option = null)
	{
		if (!extension_loaded("redis")) {
			throw new RuntimeException("插件‘redis’未加载");
		}
		$tablePrefix = Mvc::getTablePrefix();
		$this->namePrefix = Mvc::getProperty("cache.redis.namePrefix", "hefang-cms-" . $tablePrefix);
		$this->redis = new Redis();
		$this->redis->connect($option ?: "localhost");
	}

	/**
	 * 读取缓存
	 * @param string $name 名称
	 * @param null|mixed $defaultValue 默认值
	 * @return mixed 缓存值, 缓存池中没有值时返回默认值
	 */
	public function get(string $name, $defaultValue = null)
	{
		return $this->exist($this->_name($name)) ? unserialize($this->redis->get($this->_name($name))) : $defaultValue;
	}

	/**
	 * 设置缓存
	 * @param string $name 名称
	 * @param mixed $value 值
	 * @param int $expireIn 过期时间, 秒级时间戳
	 * @return void
	 */
	public function set(string $name, $value, int $expireIn = -1)
	{
		$expireIn > 0 ?
			$this->redis->set($this->_name($name), serialize($value), ['xx', 'px' => $expireIn]) :
			$this->redis->set($this->_name($name), serialize($value));
	}

	/**
	 * 判断缓存是否存在
	 * @param string $name 名称
	 * @return bool 是否存在
	 */
	public function exist(string $name): bool
	{
		return $this->redis->exists($this->_name($name)) > 0;
	}

	/**
	 * 删除缓存
	 * @param string $name 名称
	 * @return mixed 删除的缓存的值, 若缓存不存在返回null
	 */
	public function remove(string $name)
	{
		try {
			return $this->get($this->_name($name));
		} finally {
			$this->redis->del($this->_name($name));
		}
	}

	/**
	 * 清空所有缓存
	 * @return bool 是否成功
	 */
	public function clean(): bool
	{
		$names = $this->rawNames();
		$total = count($names);
		return $this->redis->del($names) === $total;
	}

	/**
	 * 获取所有缓存名称
	 * @return array 名称数组
	 */
	public function names(): array
	{
		$len = strlen($this->namePrefix);
		return array_map(function (string $name) use ($len) {
			return substr($name, $len);
		}, $this->rawNames());
	}

	public function rawNames(): array
	{
		return $this->redis->keys($this->namePrefix . "*");
	}

	/**
	 * 获取可用缓存数量
	 * @return int 缓存数量
	 */
	public function count(): int
	{
		return count($this->names());
	}

	private function _name(string $name): string
	{
		return $this->namePrefix . $name;
	}

	public function __destruct()
	{
		$this->redis->close();
	}
}
