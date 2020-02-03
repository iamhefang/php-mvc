<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");


/**
 * 纯文本视图
 * @package link\hefang\mvc\views
 */
class TextView extends BaseView
{
	const JSON = "application/json";
	const XML = "application/xml";
	const YAML = "application/yaml";
	const JAVASCRIPT = "application/javascript";
	const PLAIN = "text/plain";
	const HTML = "text/html";
	const CSS = "text/css";

	/**
	 * TextView constructor.
	 * @param string $text 内容
	 * @param string $contentType 内容类型
	 */
	public function __construct(string $text, string $contentType = TextView::PLAIN)
	{
		$this->result = $text;
		$this->contentType = $contentType;
	}

	public function compile(): BaseView
	{
		$this->isCompiled = true;
		return $this;
	}
}
