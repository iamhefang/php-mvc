<?php
defined('PHP_MVC') or die('Access Refused');

/**
 * 默认配置项, 如果项目配置项里面缺少值会使用该值
 */
return [
	'debug.enable' => false,
	'project.package' => null,
	'project.application.class' => null,
	'project.pathinfo.type' => 'PATH_INFO', //PATH_INFO, QUERY_STRING
	'project.pathinfo.querystring.key' => '_',
	'project.auth.type' => 'TOKEN', //TOKEN, SESSION, BOTH
	'project.pagination.index.name' => 'pageIndex',
	'project.pagination.size.name' => 'pageSize',
	'project.sort.key.name' => 'sortKey',
	'project.sort.type.name' => 'sortType',
	'project.custom.header' => [
		'Powered-By' => "php-mvc " . PHP_MVC
	],
	'database.enable' => false,
	'database.class' => 'link.hefang.mvc.databases.Mysql',
	'database.host' => 'localhost',
	'database.port' => null,
	'database.username' => 'root',
	'database.password' => null,
	'database.charset' => 'utf8',
	'database.database' => null,
	'database.table.prefix' => '',
	'password.salt' => null,// 密码加密时使用的盐, 该值不能使用方法生成, 必须写死,
	'default.module' => 'main',
	'default.controller' => 'home',
	'default.action' => 'index',
	'default.page.size' => 20,
	'default.charset' => 'utf-8',
	'default.theme' => 'default',
	'default.locale' => 'zh_CN',
	'default.pagination.size' => 10,
	'prefix.url.main' => '/index.php',
	'prefix.url.file' => '/files',
];
