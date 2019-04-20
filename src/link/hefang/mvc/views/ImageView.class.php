<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\helpers\ObjectHelper;


/**
 * 图片视图，需要后端渲染图片时可以返回该视图
 * @package link\hefang\mvc\views
 */
class ImageView extends BaseView
{
    /**
     * ImageView constructor.
     * @param resource $img
     * @param string $imageType
     */
    public function __construct($img, string $imageType = 'image/png')
    {
        ObjectHelper::checkNull($img, "图像资源");
        $this->result = $img;
        $this->contentType = $imageType;
    }

    public function compile(): BaseView
    {
        $this->isCompiled = true;
        return $this;
    }

    public function render()
    {
        $this->checkCompile();

        //如果不处于调试模式, 在渲染时将清除所有非 View 层的内容
        ob_clean();

        ob_start();

        //设置响应头
        header("Content-Type: $this->contentType; charset=$this->charset", true);

        //输出视图
        $func = strtolower(str_replace('/', '', $this->contentType));
        if (!function_exists($func)) {
            $func = 'imagepng';
        }
        $func($this->result);
        //输出缓冲区内容并关闭所有输出缓冲区
        while (ob_get_length() > 0 && @ob_end_flush()) ;
        imagedestroy($this->result);
        //关闭当前脚本
        exit(0);
    }
}