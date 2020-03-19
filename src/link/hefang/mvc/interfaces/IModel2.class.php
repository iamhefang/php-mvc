<?php


namespace link\hefang\mvc\interfaces;


use link\hefang\mvc\databases\BaseDb;
use link\hefang\mvc\models\ModelField;

interface IModel2
{
	/**
	 * 返回模型对应的数据库连接
	 * @return BaseDb
	 */
	public static function database(): BaseDb;

	/**
	 * 返回模型对应的数据库表
	 * @return string
	 */
	public static function table(): string;

	/**
	 * 返回模型的字段定义
	 * @return ModelField[]
	 */
	public static function fields(): array;

	/**
	 * 当前模型是否为只读
	 * @return bool
	 */
	public static function readOnly(): bool;
}
