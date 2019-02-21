<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;

class ErrorView extends BaseView
{
    private $code = 200;
    const HTTP_STATUS_CODE = [
        100 => "Continue",
        101 => "Switching Protocol",
        102 => "Processing",
        200 => "ok",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",
        208 => "Multi-Status",
        226 => "IM Used",
        404 => "Not Found"
    ];

    /**
     * ErrorView constructor.
     * @param int $code
     * @param string|null $message
     */
    public function __construct(int $code, string $message = null)
    {
        $this->code = $code;
        $this->result = ObjectHelper::nullOrDefault($message, CollectionHelper::getOrDefault(self::HTTP_STATUS_CODE, $code, $code));
    }

    public function compile(): BaseView
    {
        $this->isCompiled = true;
        return $this;
    }

    public function render()
    {
        $this->checkCompile();

        header("HTTP/1.1 $this->code $this->result");

        while (ob_get_length() > 0 && @ob_end_flush()) ;
        exit(0);
    }
}