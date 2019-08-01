<?php

namespace link\hefang\mvc\entities;

use link\hefang\helpers\ObjectHelper;
use link\hefang\interfaces\IJsonObject;
use link\hefang\interfaces\IMapObject;
use link\hefang\mvc\Mvc;

defined("PHP_MVC") or exit(404);

class Router implements IMapObject, IJsonObject
{
    private $map = [
        "module" => "",
        "controller" => "",
        "action" => "",
        "cmd" => "",
        "format" => "",
        "theme" => "",
    ];

    /**
     * Router constructor.
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $cmd
     * @param string $format
     * @param string $theme
     */
    public function __construct(
        string $module = null,
        string $controller = null,
        string $action = null,
        string $cmd = null,
        string $format = null,
        string $theme = null
    )
    {
        $this->map["module"] = ObjectHelper::nullOrDefault($module, Mvc::getDefaultModule());
        $this->map["controller"] = ObjectHelper::nullOrDefault($controller, Mvc::getDefaultController());
        $this->map["action"] = ObjectHelper::nullOrDefault($action, Mvc::getDefaultAction());
        $this->map["cmd"] = ObjectHelper::nullOrDefault($cmd, $this->getCmd());
        $this->map["format"] = ObjectHelper::nullOrDefault($format, $this->getFormat());
        $this->map["theme"] = ObjectHelper::nullOrDefault($theme, Mvc::getConfig('system|theme', Mvc::getDefaultTheme()));
    }


    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->map["module"];
    }

    /**
     * @param string $module
     * @return Router
     */
    public function setModule(string $module): Router
    {
        $this->map["module"] = self::checkUnderLine($module);
        return $this;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->map["controller"];
    }

    /**
     * @param string $controller
     * @return Router
     */
    public function setController(string $controller): Router
    {
        $this->map["controller"] = self::checkUnderLine($controller);
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->map["action"];
    }

    /**
     * @param string $action
     * @return Router
     */
    public function setAction(string $action): Router
    {
        $this->map["action"] = self::checkUnderLine($action);
        return $this;
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return $this->map["cmd"];
    }

    /**
     * @param string $cmd
     * @return Router
     */
    public function setCmd(string $cmd): Router
    {
        $this->map["cmd"] = self::checkUnderLine($cmd);
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->map["format"];
    }

    /**
     * @param string $format
     * @return Router
     */
    public function setFormat(string $format): Router
    {
        $this->map["format"] = strtolower($format);
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->map["theme"];
    }

    /**
     * @param string $theme
     * @return Router
     */
    public function setTheme(string $theme): Router
    {
        $this->map["theme"] = $theme;
        return $this;
    }

    public static function parsePath(string $path): Router
    {
        $router = new Router();
        if ($path === "/") return $router;
        //  /module/controller/action/cmd.format
        //  /module/controller/action.format
        //  /controller/action.format
        //  /action.format
        $pathes = explode("/", $path);

        $count = count($pathes);

        if ($count === 5) {
            $cmd = explode(".", $pathes[4]);

            $router->setModule($pathes[1])
                ->setController($pathes[2])
                ->setAction($pathes[3])
                ->setCmd($cmd[0]);
            if (count($cmd) > 1) {
                $router->setFormat($cmd[1]);
            }
        } else if ($count === 4) {
            $action = explode(".", $pathes[3]);

            $router->setModule($pathes[1])
                ->setController($pathes[2])
                ->setAction($action[0]);
            if (count($action) > 1) {
                $router->setFormat($action[1]);
            }
        } else if ($count === 3) {
            $action = explode(".", $pathes[2]);
            $router->setController($pathes[1])
                ->setAction($action[0]);
            if (count($action) > 1) {
                $router->setFormat($action[1]);
            }
        } else if ($count === 2) {
            $action = explode(".", $pathes[1]);
            $router->setAction($action[0]);
            if (count($action) > 1) {
                $router->setFormat($action[1]);
            }
        }

        return $router;
    }

    public static function checkUnderLine(string $str): string
    {
        if (preg_match('/^\d.*?/i', $str)) {
            return '_' . strtolower($str);
        }
        return strtolower($str);
    }

    public function toJsonString(): string
    {
        return json_encode($this->toMap(), JSON_UNESCAPED_UNICODE);
    }

    public function toMap(): array
    {
        return $this->map;
    }
}