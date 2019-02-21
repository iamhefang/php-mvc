<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");


class FileView extends BaseView
{
    private $filename;
    private $mimeType;

    /**
     * FileView constructor.
     * @param string $filename
     * @param string|null $mimeType
     */
    public function __construct(string $filename, string $mimeType = null)
    {
        $this->filename = $filename;
        $this->mimeType = $mimeType;
    }

    public function compile(): BaseView
    {
        $this->isCompiled = true;
        $this->result = file_get_contents($this->filename);
        return $this;
    }
}