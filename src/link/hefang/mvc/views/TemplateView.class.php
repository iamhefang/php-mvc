<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");


use link\hefang\helpers\CollectionHelper;
use link\hefang\mvc\exceptions\ViewNotCompiledException;
use link\hefang\mvc\exceptions\ViewNotFoundException;
use link\hefang\mvc\Mvc;
use link\hefang\site\content\models\ArticleModel;

class TemplateView extends BaseView
{
    protected $cacheFilePath = null;
    protected $includeMap = [];

    public function __construct(string $filename, array $data = null)
    {
        $this->result = PATH_THEMES . $filename;
        $this->data = is_array($data) ? $data : [];
    }

    public function compile(): BaseView
    {
        if (!file_exists($this->result)) {
            throw new ViewNotFoundException($this->result);
        }
        if (file_exists($this->cacheFilePath) && !Mvc::isDebug()) {
            $this->isCompiled = true;
            return $this;
        }
        if (!file_exists($this->cacheFilePath) || Mvc::isDebug()) {
            $this->cacheFilePath = $this->loopCompile($this->result);
        }
        $this->isCompiled = true;
        return $this;
    }


    /**
     * 循环编译视图文件
     * @param string $file
     * @return string
     */
    protected function loopCompile(string $file): string
    {
        $php = file_get_contents($file);

        $php = $this->each($php);
        $php = $this->ifelse($php);
        $php = $this->array($php);
        $php = $this->include($php, dirname($file));
        $php = $this->object($php);
        $php = $this->mvcConfig($php);
        $php = $this->func($php);
        $php = $this->php($php);
        $php = $this->variable($php);


        $php = "<?php defined('PHP_MVC') or die('Access Refused');?>\n" . $php;

        $file = str_replace(PATH_THEMES . DS, '', $file);
        $file = str_replace(DS, '_', $file);
        $file = str_replace('.php', '', $file);

        $cacheFile = PATH_CACHES . DS . $file . '.view.cache.php';

        if (!Mvc::isDebug()) {
            $php = preg_replace('/\s+/', ' ', $php);
            $php = preg_replace('/>\s+</', '> <', $php);
        }

        if (file_put_contents($cacheFile, $php) === false) {
            throw new ViewNotCompiledException("编译视图文件'{$file}'时写缓存失败");
        }

        return $cacheFile;
    }

    /**
     * 编译变量
     * @param string $php
     * @return string
     */
    protected function variable(string $php): string
    {
        return preg_replace_callback('#\{:([a-z0-9_]+)}#is', function (array $match) {
            return "<?= \${$match[1]} ?>";
        }, $php);
    }

    /**
     * 编译foreach语句
     * @param string $php
     * @return string
     */
    protected function each(string $php): string
    {
        return preg_replace_callback('#\{each:([0-9a-z_]+) as ([0-9a-z_]+)(\|([0-9a-z+]+))?}(.*?)\{endeach}#is', function (array $match) {
            $content = $match[5];
            $value = $match[4] ? "=>\${$match[4]}" : '';
            return "<?php foreach(\${$match[1]} as \${$match[2]}{$value}){ ?>{$content}<?php } ?>";
        }, $php);
    }

    /**
     * 编译ifelse语句
     * @param string $php
     * @return string
     */
    protected function ifelse(string $php): string
    {
        return preg_replace_callback('#\{if:([0-9a-z_]+)}(.*?)\{endif}#is', function (array $match) {
            $content = preg_replace_callback('/\{elseif:([0-9a-z_]+)}/is', function (array $match) {
                return "<?php } elseif(\${$match[1]}){?>";
            }, $match[2]);
            $content = preg_replace_callback('/\{else}/is', function (array $match) {
                return "<?php } else { ?>";
            }, $content);
            return "<?php if(\${$match[1]}){ ?>{$content}<?php } ?>";
        }, $php);
    }

    /**
     * 编译数组
     * @param string $php
     * @return string
     */
    protected function array(string $php): string
    {
        return preg_replace_callback('#\{([0-9a-z_]+)\[(:?[0-9a-z_]+)]}#is', function (array $match) {
            $index = $match[2];// is_numeric($match[2]) ? $match[2] : ('$' . $match[2]);
            if ($index{0} === ':') {
                $index = str_replace(':', '$', $index);
            } elseif (!is_numeric($index)) {
                $index = "'{$index}'";
            }
            return "<?= \${$match[1]}[{$index}] ?>";
        }, $php);
    }

    /**
     * 编译文件包含
     * @param string $php
     * @param string $dir
     * @return string
     */
    protected function include(string $php, string $dir): string
    {
        return preg_replace_callback('#\{(inc|incOnce):(.*?)}#is', function (array $match) use ($dir) {
            $path = $dir . DS . str_replace('/', DS, $match[2]);
            $cachePath = CollectionHelper::getOrDefault($this->includeMap, $path);
            $inc = strcasecmp('inc', $match[1]) === 0 ? 'include' : 'include_once';
            if (file_exists($cachePath)) {
                return "<?php {$inc} '{$cachePath}' ?>";
            }
            if (!file_exists($path)) {
                return "<!-- include 文件'{$path}'不存在 -->";
            }
            $cachePath = $this->loopCompile($path);
            $this->includeMap[$path] = $this->cacheFilePath;
            return "<?php {$inc} '{$cachePath}'?>";
        }, $php);
    }

    /**
     * 编译对象
     * @param string $php
     * @return string
     */
    protected function object(string $php): string
    {
        return preg_replace_callback('#\{([0-9a-z_]+)\.([0-9a-z_]+)(\(\))?}#is', function (array $match) {
            $action = $match[3] ? '' : '$';
            return "<?=\${$match[1]}->{$action}{$match[2]}{$match[3]}?>";
        }, $php);
    }

    /**
     * 编译框架全局配置项调用
     * @param string $php
     * @return string
     */
    protected function mvcConfig(string $php): string
    {
        return preg_replace_callback('#\{(mvc|config):([0-9a-z_|]{3,})(:([0-9a-z_:]+))?}#is', function (array $match) {
            $def = count($match) === 4 ? $match[4] : null;
            if ($def) {
                if ($def{0} === ':') {
                    $def = str_replace(':', '$', $def);
                } else if (!(is_numeric($def) || $def === 'false' || $def === 'true')) {
                    $def = "'{$def}'";
                }
            } else {
                $def = "''";
            }
            $type = $match[1];
            $name = $match[2];
            if ($type === 'config') {
                $name = 'theme_' . $name;
            }
            return "<?= \link\hefang\mvc\Mvc::getConfig('{$name}',{$def}) ?>";
        }, $php);
    }

    protected function php(string $php): string
    {
        return preg_replace_callback('#\{php:(.*?)}#i', function (array $match) {
            return "<?php {$match[1]} ?>";
        }, $php);
    }

    protected function func(string $php): string
    {
        return preg_replace_callback('#\{func:(.*?)}#i', function (array $match) {
            return "<?= {$match[1]} ?>";
        }, $php);
    }

    /**
     * 编译模型
     * @param string $php
     * @return string
     */
    //todo: 模型编译未完成
    protected function model(string $php): string
    {
        return preg_replace_callback('#\{model:([0-9a-z_]+):([0-9a-z_])}(.*?)\{endmodel}#is', function (array $match) {
            $id = $match[1];
            $var = $match[2];
            $content = $match[3];

            $contentNotExist = preg_replace_callback('#\{modelnotexist}#is', function ($match) {

            }, $content);

            $contentException = preg_replace_callback('#\{exception:([0-9a-z_]+)}#is', function ($match) {

            }, $content);

            try {
                $model = ArticleModel::get($id);
                if ($model->isExist()) {

                } else {

                }
            } catch (\Throwable $exception) {

            }

            $res = "<?php try { \${$var} = Model::get('{$id}'); if(\${$var}->isExist()) {{$content}} else {}}catch(\Throwable \$){} ?>";

            return "<!-- todo: 模型编译未完成 -->";
        }, $php);
    }


    public function render()
    {
        $this->checkCompile();

//        ob_start();
        extract($this->data);
        echo "\n\n\n\n\n\n";
        echo "<!-- Powered By php-mvc -->\n";
        echo "<!-- 作者: hefang -->\n";
        echo "<!-- 博客: https://hefang.link -->\n";
        echo "<!-- 微信公众号: hefangblog -->\n";
        echo "\n\n\n\n\n\n";

        include $this->cacheFilePath;

        //输出缓冲区内容并关闭所有输出缓冲区
        while (ob_get_length() > 0 && @ob_end_flush()) ;

        //关闭当前脚本
        exit(0);
    }
}