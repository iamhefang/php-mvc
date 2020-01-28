<?php


namespace link\hefang\mvc\entities;


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ParseHelper;
use link\hefang\helpers\StringHelper;

/**
 * 动作方法的文档注释
 * @package link\hefang\mvc\entities
 */
class ActionDocComment
{
	private $method = [];
	private $name = "";
	private $needLogin = false;
	private $preHandlePostData = true;

//	/**
//	 * 是否需要提前处理POST数据
//	 * @return bool
//	 */
//	public function isPreHandlePostData(): bool
//	{
//		return $this->preHandlePostData;
//	}

	private $needUnlock = false;
	private $needSuperAdmin = false;
	private $needAdmin = false;

	private function __construct(string $doc)
	{
		if (StringHelper::isNullOrBlank($doc)) {
			return;
		}
		$lines = explode("\n", $doc);

		for ($i = 1; $i < count($lines) - 1; $i++) {
			$line = ltrim($lines[$i], " *");
			$line = trim($line);
			$items = explode(" ", $line);
			switch ($items[0]) {
				//需要登录
				case "@needLogin":
					$this->needLogin = self::boolOrString($items, "需要登录");
					break;
				//支持的请求方法
				case  "@method":
					for ($idx = 1; $idx < count($items); $i++) {
						$this->method[] = strtoupper($items[$idx]);
					}
					break;
				//当前操作需要解锁
				case "@needUnlock":
					$this->needUnlock = self::boolOrString($items, "您当前已锁屏，请先解锁");
					break;
				case "@needSuperAdmin":
					$this->needSuperAdmin = self::boolOrString($items, "需要超级管理员权限");
					break;
				case "@needAdmin":
					$this->needAdmin = self::boolOrString($items, "需要管理员权限");
					break;
				case  "@preHandlePostData":
					$this->preHandlePostData = ParseHelper::parseBoolean(CollectionHelper::getOrDefault($items, 1, "true"), true);
					break;
			}
		}
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
	 * 获取当前控制器或动作名
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * 当前动作是否需求登录
	 * @return string|bool|null
	 */
	public function isNeedLogin()
	{
		return $this->needLogin;
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
	 * 当前操作是否需求解锁
	 * @return bool|string
	 */
	public function isNeedUnlock()
	{
		return $this->needUnlock;
	}

	/**
	 * 当前登录需要超级管理员权限
	 * @return bool|string
	 */
	public function isNeedSuperAdmin()
	{
		return $this->needSuperAdmin;
	}

	/**
	 * 当前登录需要管理员权限
	 * @return bool|string
	 */
	public function isNeedAdmin()
	{
		return $this->needAdmin;
	}
}
