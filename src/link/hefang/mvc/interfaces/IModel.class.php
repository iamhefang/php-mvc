<?php

namespace link\hefang\mvc\interfaces;


use link\hefang\mvc\databases\BaseDb;

interface IModel
{
    public static function table(): string;

    public static function primaryKeyFields(): array;

    public static function searchableFields(): array;

    public static function needTrimFields(): array;

    public static function bigDataFields(): array;

    /**
     * 返回模型和数据库对应的字段
     * key 为数据库对应的字段名, value 为模型字段名
     * @return array
     */
    public static function fields(): array;

    public static function database(): BaseDb;

    public static function readOnly(): bool;
}