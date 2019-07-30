<?php

namespace link\hefang\mvc\controllers;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\mvc\databases\SqlSort;
use link\hefang\mvc\entities\ApiResult;
use link\hefang\mvc\entities\CodeResult;
use link\hefang\mvc\entities\Router;
use link\hefang\mvc\interfaces\IController;
use link\hefang\mvc\interfaces\IDULG;
use link\hefang\mvc\models\BaseLoginModel;
use link\hefang\mvc\Mvc;
use link\hefang\mvc\views\BaseView;
use link\hefang\mvc\views\CodeView;
use link\hefang\mvc\views\ErrorView;
use link\hefang\mvc\views\FileView;
use link\hefang\mvc\views\ImageView;
use link\hefang\mvc\views\RedirectView;
use link\hefang\mvc\views\TemplateView;
use link\hefang\mvc\views\TextView;

/**
 * 控制器基类，所有的控制器都要直接或间接继承该类
 * @package link\hefang\mvc\controllers
 */
abstract class BaseController implements IController, IDULG
{
    /**
     * @var Router
     */
    private $router;
    /**
     * @var BaseLoginModel
     */
    private $currentLogin = null;
    private $___post = [];
    private $___request = [];

    /**
     * 返回控制器所属模块名
     * @return string
     */
    public static function module(): string
    {
        $class = explode("\\", get_called_class());
        return $class[count($class) - 3];
    }

    /**
     * 控制器名称
     * @return string
     */
    public static function name(): string
    {
        $class = explode("\\", get_called_class());
        return str_replace("Controller", "", CollectionHelper::last($class, ""));
    }

    /**
     * 标记该类是否是一个控制器
     * @return bool
     */
    public static function isController(): bool
    {
        return true;
    }

    /**
     * 获取当前路由信息
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * 设置当前路由信息
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }


    /**
     * @param string $name
     * @param null|string|int|float $defaultValue
     * @return mixed
     */
    public function _get(string $name, $defaultValue = null)
    {
        ObjectHelper::checkNull($name);
        return CollectionHelper::getOrDefault($_GET, $name, $defaultValue);
    }

    /**
     * @param string $name
     * @param null|string|int|float $defaultValue
     * @return mixed
     */
    public function _post(string $name, $defaultValue = null)
    {
        ObjectHelper::checkNull($name);
        return CollectionHelper::getOrDefault($this->___post, $name, $defaultValue);
    }

    /**
     * @param string $name
     * @param null|string|int|float $defaultValue
     * @return mixed
     */
    public function _cookie(string $name, $defaultValue = null)
    {
        ObjectHelper::checkNull($name);
        return CollectionHelper::getOrDefault($_COOKIE, $name, $defaultValue);
    }

    /**
     * @param string $name
     * @param null|string|int|float $defaultValue
     * @return mixed
     */
    public function _request(string $name, $defaultValue = null)
    {
        ObjectHelper::checkNull($name);
        return CollectionHelper::getOrDefault($this->___request, $name, $defaultValue);
    }

    /**
     * 读取session
     * @param string $name
     * @param $defaultValue
     * @return mixed|null
     */
    public function _session(string $name, $defaultValue = null)
    {
        isset($_SESSION) or session_start();
        return CollectionHelper::getOrDefault($_SESSION, $name, $defaultValue);
    }

    public function _setSession(string $name, $value): BaseController
    {
        isset($_SESSION) or session_start();
        $_SESSION[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param string|null $defaultValue
     * @return string|null
     */
    public function _header(string $name, string $defaultValue = null)
    {
        $name = "HTTP_" . strtoupper(str_replace("-", "_", $name));
        $name2 = 'REDIRECT_' . $name;

        return isset($_SERVER[$name]) ? $_SERVER[$name] : (
        isset($_SERVER[$name2]) ? $_SERVER[$name2] : $defaultValue);
    }

    /**
     * 获取当前登录用户,
     * 若没有登录用户或有登录用户但状态不对则直接重定向到需要登录
     * @param string $message
     * @param bool $needAdmin
     * @param bool $needSuperAdmin
     * @param array $needRoleIds
     * @param bool $needUnlock
     * @param bool $needDeveloper
     * @return BaseLoginModel
     */
    public function _checkLogin(string $message = null
        , bool $needAdmin = false
        , bool $needSuperAdmin = false
        , array $needRoleIds = []
        , bool $needUnlock = true
        , bool $needDeveloper = false
    ): BaseLoginModel
    {
        $login = $this->_session(BaseLoginModel::LOGIN_SESSION_KEY);
        $view = null;
        if (!($login instanceof BaseLoginModel)) {
            $view = $this->_needLogin($message);
        } else if ($needAdmin && !$login->isAdmin()) {
            $view = $this->_needAdmin($message);
        } else if ($needSuperAdmin && !$login->isSuperAdmin()) {
            $view = $this->_needSuperAdmin($message);
        } else if ($needDeveloper && !$login->isDeveloper()) {
            $view = $this->_needDeveloper($message);
        } else if ($needUnlock && $login->isLockedScreen()) {
            $view = $this->_needUnlock($message);
        } else if (!empty($needRoleIds) && !in_array($login->getRoleId(), $needRoleIds)) {
            $view = $this->_needPermission($message);
        }
        if ($view !== null) {
            $view->compile()->render();
        }
        return $login;
    }

    public function _checkAdmin(string $message = null): BaseLoginModel
    {
        return $this->_checkLogin($message, true);
    }

    public function _checkSuperAdmin(string $message = null): BaseLoginModel
    {
        return $this->_checkLogin($message, true, true);
    }

    public function _checkRoleId(array $roleIds = null, string $message = null): BaseLoginModel
    {
        return $this->_checkLogin($message, false, false, $roleIds);
    }

    /**
     * 获取当前登录用户, 不检查
     * @return BaseLoginModel|null
     */
    public function _getLogin()
    {
        return $this->_session(BaseLoginModel::LOGIN_SESSION_KEY);
    }

    public function _pageIndex(int $defaultValue = 1): int
    {
        return intval($this->_request("pageIndex", $defaultValue));
    }

    public function _pageSize(int $defaultValue = 20): int
    {
        return intval($this->_request("pageSize", $defaultValue));
    }

    /**
     * @param string|null $key
     * @param string $type
     * @param bool $nullsFirst
     * @return SqlSort|null
     */
    public function _sort(
        string $key = null,
        string $type = null,
        bool $nullsFirst = null)
    {
        $key = $this->_request("sortKey", $key);
        $type = $this->_request('sortType', $type ?: '');
        if (StringHelper::isNullOrBlank($key)) {
            return null;
        }
        return new SqlSort($key, $type, $nullsFirst);
    }

    /**
     * 获取当前访问客户端的ip地址
     * @return string
     */
    public function _ip(): string
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        return $ip;
    }

    /**
     * 获取用户代理字符串
     * @return string
     */
    public function _userAgent(): string
    {
        return $this->_header("User-Agent");
    }

    /**
     * 生成一个模板视图
     * @param array|null $data
     * @param string|null $path
     * @return BaseView
     */
    public function _template(array $data = null, string $path = null): BaseView
    {
        $theme = $this->router->getTheme();
        $controller = $this->router->getController();
        $module = $this->router->getModule();
        $action = $this->router->getAction();
        $ds = DIRECTORY_SEPARATOR;
        if (StringHelper::isNullOrBlank($path)) {
            if ($module === Mvc::getDefaultModule() && $controller === Mvc::getDefaultController()) {
                $path = $ds . join($ds, [$theme, $action]);
            } else {
                $path = $ds . join($ds, [$theme, $module, $controller, $action]);
            }
        }
        $path = str_replace("/", $ds, $path);
        if (strpos($path, $ds) === false) {
            $path = $ds . join($ds, [
                    $this->router->getTheme(),
                    $path
                ]);
        } elseif ($path{0} !== $ds) {
            $path = $ds . $this->router->getTheme() . $ds . $path;
        }

        if (!StringHelper::endsWith($path, true, ".php")) {
            $path = $path . ".php";
        }
        return new TemplateView($path, array_merge($data, [
            'themeUrl' => "/themes/{$this->router->getTheme()}"
        ]));
    }

    /**
     * 把绘制的图像返回到前端
     * @param resource $img 绘制的图像资源
     * @param string $imageType 文件类型
     * @return BaseView
     */
    public function _image($img, string $imageType = 'image/png'): BaseView
    {
        return new ImageView($img, $imageType);
    }

    public function _text(string $text, string $contentType = "text/plain"): BaseView
    {
        return new TextView($text, $contentType);
    }

    public function _redirect(string $url, bool $useJavascript = false): BaseView
    {
        return new RedirectView($url, $useJavascript);
    }

    public function _error(int $code, string $message = null): BaseView
    {
        return new ErrorView($code, $message);
    }

    /**
     * @param string $filename
     * @param string|null $mimeType
     * @return BaseView
     */
    public function _file(string $filename, string $mimeType = null): BaseView
    {
        return new FileView($filename, $mimeType);
    }

    public function _api(ApiResult $result): BaseView
    {
        ObjectHelper::checkNull($result);
        return $this->_text($result->toJsonString(), TextView::JSON);
    }

    public function _code($result, int $code = 200, string $message = null)
    {
        $message = $message ? $message : CollectionHelper::getOrDefault(CodeView::HTTP_STATUS_CODE, $code, "Unknown Code");
        return new CodeView(new CodeResult($code, $message, $result));
    }

    /**
     * 请求成功
     * @param string $result
     * @return CodeView
     */
    public function _codeOk($result = "ok")
    {
        return $this->_code($result, 200, "ok");
    }

    /**
     * 内容新建成功
     * @param string $result
     * @return CodeView
     */
    public function _codeCreated($result = "新建成功")
    {
        return $this->_code($result, 201);
    }

    /**
     * 请求的内容未找到
     * @param string $result
     * @return CodeView
     */
    public function _codeNotFound($result = "请求的内容未找到")
    {
        return $this->_code($result, 404);
    }

    /**
     * 权限不够
     * @param string $result
     * @return CodeView
     */
    public function _codeForbidden($result = "您无权访问该内容")
    {
        return $this->_code($result, 403);
    }

    /**
     * 当前需要登录
     * @param string $result
     * @return CodeView
     */
    public function _codeUnauthorized($result = "您需要登录后才能访问")
    {
        return $this->_code($result, 401);
    }


    public function _apiSuccess($result = "ok"): BaseView
    {
        return $this->_api(new ApiResult(true, $result));
    }

    public function _apiFailed(
        string $reason,
        bool $needLogin = false
        , bool $needPassword = false
        , bool $needAdmin = false
        , bool $needSuperAdmin = false
        , bool $needPermission = false
        , bool $needUnlock = false
        , bool $needDeveloper = false
    ): BaseView
    {
        return $this->_api(new ApiResult(
            false, $reason, $needLogin,
            $needPassword, $needAdmin, $needSuperAdmin,
            $needPermission, $needUnlock, $needDeveloper
        ));
    }

    public function _needLogin(string $message = null): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "您当前未登录或登录已超时, 请登录后重试" : $message;
        return $this->_apiFailed($message, true);
    }

    public function _needSuperAdmin(string $message = null): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "当前功能只有超级管理员才能使用" : $message;
        return $this->_apiFailed($message, false, false, true, true);
    }

    public function _needAdmin(string $message = null): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "当前功能只有管理员才能使用" : $message;
        return $this->_apiFailed($message, false, false, true);
    }

    public function _needPassword(string $message = "请输入密码"): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "请输入密码" : $message;
        return $this->_apiFailed($message, false, true);
    }

    public function _needPermission(string $message = null): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "您无权使用该功能" : $message;
        return $this->_apiFailed($message, false, false, false, false, true);
    }

    public function _needUnlock(string $message = null): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "您已锁屏, 请先解锁" : $message;
        return $this->_apiFailed($message, false, false, false, false, false, true);
    }

    public function _needDeveloper(string $message = null): BaseView
    {
        $message = StringHelper::isNullOrBlank($message) ? "该功能正在开发中" : $message;
        return $this->_apiFailed($message, false, false, false, false, false, false, true);
    }

    public function _404(): BaseView
    {
        return $this->_error(404, "Not Found");
    }

    public function _exception(\Throwable $e, string $message = null, string $title = null): BaseView
    {
        Mvc::getLogger()->error($title ?: '出现异常', $e->getMessage(), $e);
        return $this->_apiFailed($message ?: $e->getMessage());
    }

    public function _null(): BaseView
    {
        return $this->_text("");
    }

    public function insert(): BaseView
    {
        return $this->_404();
    }

    public function delete(): BaseView
    {
        return $this->_404();
    }

    public function update(): BaseView
    {
        return $this->_404();
    }

    public function list(): BaseView
    {
        return $this->_404();
    }

    public function get(string $id = null): BaseView
    {
        return $this->_404();
    }
}