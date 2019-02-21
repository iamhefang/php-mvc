<?php
/**
 * Created by IntelliJ IDEA.
 * User: hefang
 * Date: 2018/12/10
 * Time: 09:52
 */

namespace link\hefang\mvc\exceptions;


class ViewNotFoundException extends \RuntimeException
{
    public function __construct(string $filename)
    {
        parent::__construct("视图文件'$filename'未找到");
    }
}