<?php

namespace link\hefang\mvc\exceptions;


use RuntimeException;

class ViewNotFoundException extends RuntimeException
{
	public function __construct(string $filename)
	{
		parent::__construct("视图文件'$filename'未找到");
	}
}
