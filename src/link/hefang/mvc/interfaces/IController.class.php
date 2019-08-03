<?php

namespace link\hefang\mvc\interfaces;


interface IController
{
	public static function module(): string;

	public static function name(): string;

	public static function isController(): bool;

}
