<?php

namespace link\hefang\mvc\entities;


use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;

class Pager implements IJsonObject, IMapObject, \JsonSerializable
{
    private $total = 0;
    private $pageIndex = 1;
    private $pageSize = 20;
    private $data = [];

    /**
     * Pager constructor.
     * @param int $total
     * @param int $pageIndex
     * @param int $pageSize
     * @param array $data
     */
    public function __construct(int $total, int $pageIndex, int $pageSize, array $data)
    {
        $this->total = $total;
        $this->pageIndex = $pageIndex;
        $this->pageSize = $pageSize;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPageIndex(): int
    {
        return $this->pageIndex;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function toJsonString(): string
    {
        return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
    }

    public function toMap(): array
    {
        return [
            "pageIndex" => $this->pageIndex,
            "pageSize" => $this->pageSize,
            "total" => $this->total,
            "data" => $this->data
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toMap();
    }
}