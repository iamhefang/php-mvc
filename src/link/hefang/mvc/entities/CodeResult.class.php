<?php


namespace link\hefang\mvc\entities;


use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\databases\Mysql;
use link\hefang\mvc\Mvc;

/**
 * 状态码视图
 * @package link\hefang\mvc\entities
 */
class CodeResult implements IJsonObject, IMapObject, \JsonSerializable
{
    private $code = 200;
    private $message = '';
    private $result;

    /**
     * CodeResult constructor.
     * @param int $code 状态码
     * @param string $message 消息
     * @param string|array|null $result 响应内容
     */
    public function __construct(int $code, string $message, $result)
    {
        $this->code = $code;
        $this->message = $message;
        $this->result = $result;
    }


    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    public function toJsonString(): string
    {
        return json_encode($this->toMap());
    }

    public function toMap(): array
    {
        $map = [
            'code' => $this->code,
            'message' => $this->message,
            'result' => $this->result
        ];
        if (Mvc::isDebug()) {
            $map['debug'] = [
                'classes' => count(get_declared_classes()),
                'files' => count(get_included_files()),
                'sqls' => count(Mysql::getExecutedSqls()),
                'time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 3)
            ];
        }
        return $map;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toMap();
    }
}