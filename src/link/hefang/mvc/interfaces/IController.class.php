<?php
/**
 * Created by IntelliJ IDEA.
 * User: hefang
 * Date: 2018/12/4
 * Time: 07:59
 */

namespace link\hefang\mvc\interfaces;


interface IController
{
    public static function module(): string;

    public static function name(): string;

    public static function isController(): bool;

}