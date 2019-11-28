<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\mvc\exceptions\ViewNotCompiledException;
use link\hefang\mvc\Mvc;

/**
 * 视图基类，所有视图都要直接或间接继承该类
 * @package link\hefang\mvc\views
 */
abstract class BaseView
{
	protected $contentType;
	protected $isCompiled = false;
	protected $result = "";
	protected $data = [];
	protected $charset = "UTF-8";

	/**
	 * 当前视图的类型
	 * @return string
	 */
	public function getContentType(): string
	{
		return $this->contentType;
	}

	/**
	 * 当前视图大小
	 * @return int
	 */
	public function getContentLength(): int
	{
		return strlen($this->result);
	}

	/**
	 * 检查当前视图是否已编译
	 * @throws ViewNotCompiledException
	 */
	protected function checkCompile()
	{
		if (!$this->isCompiled) throw new ViewNotCompiledException();
	}

	/**
	 * 渲染视图
	 * @throws ViewNotCompiledException
	 */
	public function render()
	{
		$this->checkCompile();

		//如果不处于调试模式, 在渲染时将清除所有非 View 层的内容
		Mvc::isDebug() or ob_clean();

		ob_start();

		//设置响应头
		header("Content-Type: $this->contentType; charset=$this->charset", true);
		$customHeaders = Mvc::getProperty("project.custom.header", []);
		foreach ($customHeaders as $name => $value) {
			header($name, $value);
		}
		//输出视图
		echo $this->result;

		//输出缓冲区内容并关闭所有输出缓冲区
		while (ob_get_length() > 0 && @ob_end_flush()) ;

		//关闭当前脚本
		exit(0);
	}

	/**
	 * 编译视图
	 * @return BaseView
	 */
	public abstract function compile(): BaseView;
}
