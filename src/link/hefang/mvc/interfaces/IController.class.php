<?php

namespace link\hefang\mvc\interfaces;


interface IController
{
	/**
	 * 返回当前控制器所有的模块名
	 * @return string
	 */
	public static function module(): string;

	/**
	 * 返回当前控制器名称
	 * @return string
	 */
	public static function name(): string;

	/**
	 * 返回当前类是否为控制器
	 * @return bool
	 */
	public static function isController(): bool;
}
