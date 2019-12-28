<?php


namespace link\hefang\mvc\entities;


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\mvc\exceptions\DocCommentException;

/**
 * 动作方法的文档注释
 * @package link\hefang\mvc\entities
 */
class ActionDocComment
{
	private $method = [];
	private $needLogin = null;

	private function __construct(string $doc)
	{
		$lines = explode("\n", $doc);

		for ($i = 1; $i < count($lines) - 1; $i++) {
			$line = ltrim($lines[$i], " *");
			$line = trim($line);
			$items = explode(" ", $line);
			switch ($items[0]) {
				case "@needLogin":
					$this->needLogin = CollectionHelper::last($items, "需要登录");
					break;
				case  "@method":
					for ($idx = 1; $idx < count($items); $i++) {
						$this->method[] = $items[$idx];
					}
					break;
			}
		}
	}

	/**
	 * @param string $doc
	 * @return ActionDocComment
	 * @throws DocCommentException
	 */
	static function parse(string $doc): ActionDocComment
	{
		if (StringHelper::isNullOrBlank($doc)) {
			throw new DocCommentException("要解析的文档注释不能为空");
		}
		return new ActionDocComment($doc);
	}

	/**
	 * 当前动作是否需求登录
	 * @return string|null
	 */
	public function getNeedLogin()
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

}
