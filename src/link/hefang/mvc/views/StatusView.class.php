<?php


namespace link\hefang\mvc\views;

use link\hefang\mvc\results\StatusResult;

class StatusView extends BaseView
{
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
		304 => "Not Modified",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		409 => "Conflict",
		410 => "Gone",
		415 => "Unsupported Media Type",
		423 => "Locked",
		451 => "Unavailable For Legal Reasons",
		499 => "Not Success Of Unknown Reason",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		504 => "Gateway Timeout"
	];
	private $status = 200;
	private $message = '';

	public function __construct(StatusResult $result)
	{
		$this->status = $result->getStatus();
		$this->message = $result->getMessage() ? $result->getMessage() : $this->message;
		$this->result = $result;
		$this->contentType = "application/json";
	}

	public function compile(): BaseView
	{
		$this->isCompiled = true;
		$this->result = json_encode($this->result, JSON_UNESCAPED_UNICODE);
		return $this;
	}

	public function render()
	{
		$this->checkCompile();

		header("HTTP/1.1 $this->status $this->message");

		parent::render();
	}
}
