<?php


namespace link\hefang\mvc\views;


class CodeView extends BaseView
{
    private $code = 200;
    private $message = '';
    private $result = '';

    public function __construct(int $code, string $message = null, $result = null)
    {
        $this->code = $code;
        $this->message = $message || $this->message;
        $this->result = $result || $this->result;
    }

    public function compile(): BaseView
    {
        $this->isCompiled = true;

        return $this;
    }

    public function render()
    {
        $this->checkCompile();
    }
}