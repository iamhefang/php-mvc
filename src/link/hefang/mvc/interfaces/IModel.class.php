<?php

namespace link\hefang\mvc\interfaces;


use link\hefang\mvc\databases\BaseDb;

interface IModel
{
	/**
	 * 返回该模型对应的表名
	 * @return string
	 */
	public static function table(): string;

	/**
	 * 返回主键
	 * @return array
	 */
	public static function primaryKeyFields(): array;

	/**
	 * 返回可被搜索的字段名
	 * @return array
	 */
	public static function searchableFields(): array;

	/**
	 * 返回需要对值执行trim的字段名
	 * @return array
	 */
	public static function needTrimFields(): array;

	/**
	 * 大数据字段名, 大数据字段在列表查找时将不在结果中显示值
	 * @return array
	 */
	public static function bigDataFields(): array;

	/**
	 * 返回模型和数据库对应的字段
	 * key 为数据库对应的字段名, value 为模型字段名
	 * key 不写或为数字时将被框架忽略, 使用value值做为key
	 * @return array
	 */
	public static function fields(): array;

	/**
	 * 返回该模型对对应的数据库
	 * @return BaseDb
	 */
	public static function database(): BaseDb;

	/**
	 * 该模型是否只读
	 * @return bool
	 */
	public static function readOnly(): bool;
}
