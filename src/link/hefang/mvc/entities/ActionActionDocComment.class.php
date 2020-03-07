<?php


namespace link\hefang\mvc\entities;


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ParseHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\interfaces\IMapObject;

/**
 * 动作方法的文档注释
 * @package link\hefang\mvc\entities
 */
class ActionDocComment implements IMapObject
{
	private $name = "";
	private $path = "";
	private $method = [];
	private $preHandlePostData = true;

//	/**
//	 * 是否需要提前处理POST数据
//	 * @return bool
//	 */
//	public function isPreHandlePostData(): bool
//	{
//		return $this->preHandlePostData;
//	}

	private function __construct(string $doc)
	{
		if (StringHelper::isNullOrBlank($doc)) {
			return;
		}
		$lines = explode("\n", $doc);

		for ($i = 1; $i < count($lines) - 1; $i++) {
			$line = trim($lines[$i], " *\t\n\r\0\x0B");
//			$line = rtrim($line);
			$items = explode(" ", $line);
			switch ($items[0]) {
				case  "@method"://支持的请求方法
					for ($idx = 1; $idx < count($items); $idx++) {
						$this->method[] = strtoupper($items[$idx]);
					}
					break;
				case "@name"://控制器或动作名
					$this->name = CollectionHelper::getOrDefault($items, 1);
					break;
				case "@path"://动作访问路径
					$this->path = CollectionHelper::getOrDefault($items, 1);
					break;
				case  "@preHandlePostData":
					$this->preHandlePostData = ParseHelper::parseBoolean(CollectionHelper::getOrDefault($items, 1, "true"), true);
					break;
			}
		}
	}

	/**
	 * 解析控制器构造方法和动作上的文档注释
	 * @param string $doc 要解析的文档该校注释
	 * @return ActionDocComment 解析成功返回文档注释对象
	 */
	static function parse(string $doc): ActionDocComment
	{
//		if (StringHelper::isNullOrBlank($doc)) {
//			throw new DocCommentException("要解析的文档注释不能为空");
//		}
		return new ActionDocComment($doc);
	}

	/**
	 * @param string $doc
	 * @return array
	 */
	static function parse2array(string $doc): array
	{
		if (StringHelper::isNullOrBlank($doc)) {
//			throw new DocCommentException("注释文档不能为空");
			return [];
		}
		$lines = explode("\n", $doc);
		$docArray = [];
		for ($i = 1; $i < count($lines) - 1; $i++) {
			$line = ltrim($lines[$i], " *");
			$line = trim($line);
			$items = explode(" ", $line);
			$docArray[substr($items[0], 1)] = self::boolOrString($items, "");
		}
		return $docArray;
	}

	private static function boolOrString(array $items, string $defVal)
	{
		if (count($items) < 2) {
			return $defVal;
		}
		$msg = $items[1];
		if (strcasecmp($msg, "false") === 0) {
			return false;
		}
		return StringHelper::isNullOrBlank($msg) || strcasecmp($msg, "true") === 0 ? $defVal : $msg;
	}

	public function toMap(): array
	{
		return [
			"name" => $this->getName(),
			"method" => $this->getMethod(),
			"path" => $this->getPath()
		];
	}

	/**
	 * 获取当前控制器或动作名
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * 获取动作支持的请求方法
	 * @return array
	 */
	public function getMethod(): array
	{
		return $this->method;
	}

	/**
	 * @return string|null
	 */
	public function getPath()
	{
		return $this->path;
	}
}
