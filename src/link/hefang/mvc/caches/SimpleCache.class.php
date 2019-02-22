<?php
namespace link\hefang\mvc\caches;
defined('PHP_MVC') or die("Access Refused");


use link\hefang\caches\CacheItem;
use link\hefang\helpers\FileHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\interfaces\ICache;
use link\hefang\mvc\exceptions\InvalidCachePath;
use link\hefang\mvc\Mvc;

class SimpleCache implements ICache
{
    private $path = "";

    /**
     * 读取缓存
     * @param string $name 名称
     * @param null|mixed $defaultValue 默认值
     * @return mixed 缓存值, 缓存池中没有值时返回默认值
     */
    public function get(string $name, $defaultValue = null)
    {
        if (StringHelper::isNullOrBlank($name)) return $defaultValue;
        $file = $this->makeCacheFileName($name);
        if (!is_file($file)) {
            return $defaultValue;
        }
        $cache = CacheItem::fromSerializedString(file_get_contents($file));
        if ($cache->getExpireIn() > 0 && $cache->getExpireIn() < time()) {
            $this->remove($name, false);
            return $defaultValue;
        }
        return $cache->getValue();
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
        ObjectHelper::checkNull($name);
        $file = $this->makeCacheFileName($name);
        $cache = new CacheItem($value, $expireIn);
        $string = serialize($cache);
        file_put_contents($file, $string);
    }

    /**
     * 判断缓存是否存在
     * @param string $name 名称
     * @return bool 是否存在
     */
    public function exist(string $name): bool
    {
        if (StringHelper::isNullOrBlank($name)) return false;
        $file = $this->makeCacheFileName($name);
        return file_exists($file);
    }

    /**
     * 删除缓存
     * @param string $name 名称
     * @param bool $return 是否返回被删除的值
     * @return mixed|bool
     * 若 return 为 true 返回删除的缓存的值, 若缓存不存在返回null
     * 若 return 为 false 返回是否删除成功
     */
    public function remove(string $name, bool $return = true)
    {
        if (!$this->exist($name)) return null;
        $value = null;
        $file = $this->makeCacheFileName($name);
        if ($return) {
            $value = $this->get($name, null);
            unlink($file);
            return $value;
        } else {
            return unlink($file);
        }
    }

    /**
     * 清空所有缓存
     * @return bool 是否成功
     */
    public function clean(): bool
    {
        return FileHelper::cleanDir($this->path) > 0;
    }

    /**
     * 获取所有缓存名称
     * @return array 名称数组
     */
    public function names(): array
    {
        return FileHelper::listFiles($this->path, function (string $file) {
            return StringHelper::endsWith($file, true, ".cache");
        });
    }

    /**
     * 获取可用缓存数量
     * @return int 缓存数量
     */
    public function count(): int
    {
        return count($this->names());
    }

    private function makeCacheFileName(string $name)
    {
        return $this->path . DS . md5($name) . ".cache";
    }

    /**
     * ICache constructor.
     * @param string $option 缓存选项, 缓存的实现类所需的选项不一定相同.
     * 参数为 string 类型, 实现缓存类时可自行把参数做为 json 或 xml 解析
     */
    public function __construct(string $option = null)
    {
        StringHelper::isNullOrBlank($option) and $option = sys_get_temp_dir() . DS . "php-mvc-caches";

        if (!file_exists($option)) {
            mkdir($option, 0770, true);
        }

        if (!is_dir($option)) {
            throw new InvalidCachePath("缓存保存路径不是目录");
        }

        if (!is_readable($option)) {
            throw new InvalidCachePath("缓存保存路径不可读");
        }

        if (!is_writable($option)) {
            throw new InvalidCachePath("缓存保存路径不可写");
        }

//        Mvc::getLogger()->notice("当前缓存保存路径", $option);

        $this->path = $option;
    }
}