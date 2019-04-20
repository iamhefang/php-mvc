<?php

namespace link\hefang\mvc\entities;


use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\databases\Mysql;
use link\hefang\mvc\Mvc;

class ApiResult implements IJsonObject, IMapObject, \JsonSerializable
{
    private $code = 200;
    private $success = false;
    private $result = null;
    private $needLogin = false;
    private $needPassword = false;
    private $needAdmin = false;
    private $needSuperAdmin = false;
    private $needPermission = false;
    private $needUnlock = false;
    private $needDeveloper = false;

    /**
     * ApiResult constructor.
     * @param bool $success
     * @param null $result
     * @param bool $needLogin
     * @param bool $needPassword
     * @param bool $needAdmin
     * @param bool $needSuperAdmin
     * @param bool $needPermission
     * @param bool $needUnlock
     * @param bool $needDeveloper
     * @param int|null $code
     */
    public function __construct(
        bool $success = false,
        $result = null,
        bool $needLogin = false,
        bool $needPassword = false,
        bool $needAdmin = false,
        bool $needSuperAdmin = false,
        bool $needPermission = false,
        bool $needUnlock = false,
        bool $needDeveloper = false,
        int $code = null
    )
    {
        $this->success = $success;
        $this->result = $result;
        $this->needLogin = $needLogin;
        $this->needPassword = $needPassword;
        $this->needAdmin = $needAdmin;
        $this->needSuperAdmin = $needSuperAdmin;
        $this->needPermission = $needPermission;
        $this->needUnlock = $needUnlock;
        $this->needDeveloper = $needDeveloper;
        $this->code = $success ? 200 : $code;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return ApiResult
     */
    public function setSuccess(bool $success): ApiResult
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param null $result
     * @return ApiResult
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedLogin(): bool
    {
        return $this->needLogin;
    }

    /**
     * @param bool $needLogin
     * @return ApiResult
     */
    public function setNeedLogin(bool $needLogin): ApiResult
    {
        $this->needLogin = $needLogin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedPassword(): bool
    {
        return $this->needPassword;
    }

    /**
     * @param bool $needPassword
     * @return ApiResult
     */
    public function setNeedPassword(bool $needPassword): ApiResult
    {
        $this->needPassword = $needPassword;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedAdmin(): bool
    {
        return $this->needAdmin;
    }

    /**
     * @param bool $needAdmin
     * @return ApiResult
     */
    public function setNeedAdmin(bool $needAdmin): ApiResult
    {
        $this->needAdmin = $needAdmin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedSuperAdmin(): bool
    {
        return $this->needSuperAdmin;
    }

    /**
     * @param bool $needSuperAdmin
     * @return ApiResult
     */
    public function setNeedSuperAdmin(bool $needSuperAdmin): ApiResult
    {
        $this->needSuperAdmin = $needSuperAdmin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedPermission(): bool
    {
        return $this->needPermission;
    }

    /**
     * @param bool $needPermission
     * @return ApiResult
     */
    public function setNeedPermission(bool $needPermission): ApiResult
    {
        $this->needPermission = $needPermission;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedUnlock(): bool
    {
        return $this->needUnlock;
    }

    /**
     * @param bool $needUnlock
     * @return ApiResult
     */
    public function setNeedUnlock(bool $needUnlock): ApiResult
    {
        $this->needUnlock = $needUnlock;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedDeveloper(): bool
    {
        return $this->needDeveloper;
    }

    /**
     * @param bool $needDeveloper
     * @return ApiResult
     */
    public function setNeedDeveloper(bool $needDeveloper): ApiResult
    {
        $this->needDeveloper = $needDeveloper;
        return $this;
    }


    public function toJsonString(): string
    {
        return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
    }

    public function toMap(): array
    {
        $map = [
            "success" => $this->success,
            "result" => $this->result,
            "code" => $this->code
        ];
        if ($this->needLogin) {
            $map["needLogin"] = true;
        }
        if ($this->needPassword) {
            $map["needPassword"] = true;
        }
        if ($this->needAdmin) {
            $map["needAdmin"] = true;
        }
        if ($this->needSuperAdmin) {
            $map["needSuperAdmin"] = true;
        }
        if ($this->needPermission) {
            $map["needPermission"] = true;
        }
        if ($this->needUnlock) {
            $map["needUnlock"] = true;
        }
        if ($this->needDeveloper) {
            $map["needDeveloper"] = true;
        }
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