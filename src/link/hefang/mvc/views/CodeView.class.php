<?php


namespace link\hefang\mvc\views;


use link\hefang\mvc\entities\CodeResult;

class CodeView extends BaseView
{
	private $code = 200;
	private $message = '';
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
		401 => "Unauthorized",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		423 => "Locked",
		500 => "Internal Server Error"
	];

	public function __construct(CodeResult $result)
	{
		$this->code = $result->getCode();
		$this->message = $result->getMessage() || $this->message;
		$this->result = $result->getResult() || $this->result;
	}

	public function compile(): BaseView
	{
		$this->isCompiled = true;

		return $this;
	}

	public function render()
	{
		$this->checkCompile();

		header("HTTP/1.1 $this->code $this->message");

		parent::render();
	}
}
