<?php


namespace link\hefang\mvc\entities;


use link\hefang\helpers\StringHelper;
use link\hefang\mvc\exceptions\DocCommentException;

class DocComment
{
	private $returnType;
	private $params = [];

	private function __construct(string $doc)
	{

	}

	/**
	 * @return string|null
	 */
	public function getReturnType()
	{
		return $this->returnType;
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @param string $doc
	 * @return DocComment
	 * @throws DocCommentException
	 */
	static function parse(string $doc): DocComment
	{
		if (StringHelper::isNullOrBlank($doc)) {
			throw new DocCommentException("要解析的文档注释不能为空");
		}
		return new DocComment($doc);
	}
}
