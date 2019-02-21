<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");


class TextView extends BaseView
{
    const PLAIN = "text/plain";
    const HTML = "text/html";
    const JSON = "application/json";
    const XML = "application/xml";
    const CSS = "text/css";
    const JAVASCRIPT = "application/javascript";

    /**
     * TextView constructor.
     * @param string $text 内容
     * @param string $contentType 内容类型
     */
    public function __construct(string $text, string $contentType = TextView::PLAIN)
    {
        $this->result = $text;
        $this->contentType = $contentType;
    }

    public function compile(): BaseView
    {
        $this->isCompiled = true;
        return $this;
    }
}