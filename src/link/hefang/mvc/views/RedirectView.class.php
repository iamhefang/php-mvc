<?php

namespace link\hefang\mvc\views;

use link\hefang\mvc\Mvc;

defined('PHP_MVC') or die("Access Refused");


class RedirectView extends BaseView
{

    private $useJavascript = false;

    /**
     * RedirectView constructor.
     * @param string $url
     * @param bool $useJavascript
     */
    public function __construct(string $url, bool $useJavascript)
    {
        $this->useJavascript = $useJavascript;
        if ($useJavascript) {
            $this->result = <<<HTML
<!doctype html>
<html lang="zh">
<head>
    <meta charset="$this->charset">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge,chrome=1">
    <meta http-equiv="refresh" content="0;url=$url"> 
    <title>正在跳转...</title>
</head>
<body>
<a href="$url" target="_self" style="display: none;" id="link">立即跳转</a>
<script>setTimeout(function() {
    var link = document.getElementById("link")
    link.style.display="block";
},2000);</script>
</body>
</html>
HTML;
        } else {
            $this->result = $url;
        }
    }

    public function compile(): BaseView
    {
        $this->isCompiled = true;
        return $this;
    }

    public function render()
    {
        $this->checkCompile();
        Mvc::isDebug() or @ob_clean();
        if ($this->useJavascript) {
            echo $this->result;
            @ob_end_flush();
        } else {
            header("Location: $this->result");
            @ob_end_flush();
        }
        exit(0);
    }
}