<?php

namespace link\hefang\mvc\caches;
defined('PHP_MVC') or die("Access Refused");


use link\hefang\interfaces\ICache;

//todo: radius 缓存待实现
class RadiusCache implements ICache
{

	/**
	 * ICache constructor.
	 * @param string $option 缓存选项, 缓存的实现类所需的选项不一定相同.
	 * 参数为 string 类型, 实现缓存类时可自行把参数做为 json 或 xml 解析
	 */
	public function __construct(string $option = null)
	{
		parent::__construct($option);
	}

	/**
	 * 读取缓存
	 * @param string $name 名称
	 * @param null|mixed $defaultValue 默认值
	 * @return mixed 缓存值, 缓存池中没有值时返回默认值
	 */
	public function get(string $name, $defaultValue = null)
	{
		// TODO: Implement get() method.
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
		// TODO: Implement set() method.
	}

	/**
	 * 判断缓存是否存在
	 * @param string $name 名称
	 * @return bool 是否存在
	 */
	public function exist(string $name): bool
	{
		// TODO: Implement exist() method.
	}

	/**
	 * 删除缓存
	 * @param string $name 名称
	 * @return mixed 删除的缓存的值, 若缓存不存在返回null
	 */
	public function remove(string $name)
	{
		// TODO: Implement remove() method.
	}

	/**
	 * 清空所有缓存
	 * @return bool 是否成功
	 */
	public function clean(): bool
	{
		// TODO: Implement clean() method.
	}

	/**
	 * 获取所有缓存名称
	 * @return array 名称数组
	 */
	public function names(): array
	{
		// TODO: Implement names() method.
	}

	/**
	 * 获取可用缓存数量
	 * @return int 缓存数量
	 */
	public function count(): int
	{
		// TODO: Implement count() method.
	}
}
