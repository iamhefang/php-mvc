<?php

namespace link\hefang\mvc\views;
defined('PHP_MVC') or die("Access Refused");


use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;

class ErrorView extends BaseView
{
	private $code = 200;


	/**
	 * ErrorView constructor.
	 * @param int $code
	 * @param string|null $message
	 */
	public function __construct(int $code, string $message = null)
	{
		$this->code = $code;
		$this->result = ObjectHelper::nullOrDefault($message, CollectionHelper::getOrDefault(StatusView::HTTP_STATUS_CODE, $code, $code));
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
		$this->flushHeaders();

		while (ob_get_length() > 0 && @ob_end_flush()) ;
		exit(0);
	}
}
