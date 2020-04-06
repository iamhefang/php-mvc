<?php


namespace link\hefang\mvc\helpers;


use link\hefang\mvc\databases\Mysql;
use link\hefang\mvc\Mvc;

class DebugHelper
{
	private static $plugins = [];

	/**
	 * @param array $plugins
	 */
	public static function addPlugins(array $plugins)
	{
		self::$plugins = array_merge(self::$plugins, $plugins);
	}

	/**
	 * @param mixed $plugins
	 */
	public static function addPlugin($plugins)
	{
		self::$plugins[] = $plugins;
	}

	public static function apiDebugField(array &$map)
	{
		if (Mvc::isDebug()) {
			$map['debug'] = [
				// 已定义的类
				'declaredClasses' => get_declared_classes(),
				// 已包含的文件
				'includedFiles' => get_included_files(),
				// 本次请求执行的SQL语句
				'executedSQL' => Mysql::getExecutedSqls(),
				// 本次请求加载的模块
				'loadedModules' => [],
				// 本次请求加载的插件
				'loadedPlugins' => self::$plugins,
				// 服务器信息
				'serverHost' => $_SERVER["HTTP_HOST"],
				//服务器名称
				'serverName' => $_SERVER["SERVER_NAME"],
				// 服务器操作系统
				'serverOS' => PHP_OS,
				// 本次请求持续时间
				'duration' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000)
			];
		}
	}
}
