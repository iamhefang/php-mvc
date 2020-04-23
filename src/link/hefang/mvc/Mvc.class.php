<?php

namespace link\hefang\mvc;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\entities\Properties;
use link\hefang\enums\LogLevel;
use link\hefang\helpers\ClassHelper;
use link\hefang\helpers\CollectionHelper;
use link\hefang\helpers\ObjectHelper;
use link\hefang\helpers\ParseHelper;
use link\hefang\helpers\StringHelper;
use link\hefang\helpers\TimeHelper;
use link\hefang\interfaces\ICache;
use link\hefang\interfaces\ILogger;
use link\hefang\mvc\caches\SimpleCache;
use link\hefang\mvc\controllers\BaseController;
use link\hefang\mvc\databases\BaseDb;
use link\hefang\mvc\entities\ActionDocComment;
use link\hefang\mvc\entities\Router;
use link\hefang\mvc\exceptions\ActionNotFoundException;
use link\hefang\mvc\exceptions\ControllerNotFoundException;
use link\hefang\mvc\exceptions\DbException;
use link\hefang\mvc\exceptions\MethodNotAllowException;
use link\hefang\mvc\exceptions\PhpErrorException;
use link\hefang\mvc\interfaces\IApplication;
use link\hefang\mvc\logger\SimpleFileLogger;
use link\hefang\mvc\views\BaseView;
use link\hefang\mvc\views\FileView;
use link\hefang\mvc\views\TextView;
use ReflectionClass;
use Throwable;

class Mvc
{
	const VERSION = PHP_MVC;

	private static $pathInfoType = "PATH_INFO";

	private static $postRawJSON = null;

	private static $globalConfig = [];

	/**
	 * @var Properties
	 */
	private static $properties = null;
	private static $settings = [];

	private static $urlRoot = "";

	private static $developers = [];

	private static $fileUrlPrefix = "/files/";
	private static $urlPrefix = '/index.php';

	/**
	 * 上传文件保存路径
	 * @var string
	 */
	private static $fileSavePath = "";

	private static $projectPackage = "";

	private static $defaultModule = "main";
	private static $defaultController = "home";
	private static $defaultAction = "index";
	private static $defaultCharset = "UTF-8";
	private static $defaultPageSize = 10;
	private static $defaultTheme = "default";
	private static $defaultLocale = "zh_CN";

	private static $passwordSalt = "";

	private static $authType = "SESSION";

	/**
	 * @var BaseDb
	 */
	private static $database;
	private static $tablePrefix = "";

	private static $controllers = [];
	private static $actions = [];

	private static $debug = false;

	/**
	 * @var IApplication
	 */
	private static $application;

	/**
	 * @var ILogger
	 */
	private static $logger = null;

	/**
	 * @var ICache
	 */
	private static $cache = null;

	/**
	 * 读取全局配置项, Application::init()返回的配置项和Mvc::addConfig添加的配置项
	 * @param string $key
	 * @param mixed|null $defaultValue
	 * @return mixed|null
	 */
	public static function getConfig(string $key, $defaultValue = null)
	{
		return CollectionHelper::getOrDefault(self::$globalConfig, $key, $defaultValue);
	}

	/**
	 * 整体替换配置项
	 * @param array $config
	 */
	public static function putConfig(array $config)
	{
		self::$globalConfig = $config;
	}

	/**
	 * 添加一个配置项， 可使用Mvc::getConfig()读取到
	 * @param string $name
	 * @param $value
	 */
	public static function addConfig(string $name, $value)
	{
		self::$globalConfig[$name] = $value;
	}

	public static function errorHandler(int $errno, string $errstr, string $errfile = null, int $errline = -1)
	{
		$e = new PhpErrorException($errno, $errstr, $errfile, $errline);
		self::exceptionHandler($e);
	}

	public static function exceptionHandler(Throwable $exception)
	{
		self::$logger and self::$logger->error($exception->getMessage(), $exception->getMessage(), $exception);
		if (self::$application != null) {
			$view = self::$application->onException($exception);
			if ($view !== null) {
				$view->compile()->render();
			} else if (Mvc::isDebug()) {
				include "templates/exception.php";
				exit(0);
			} else {
				include "templates/error.php";
				exit(0);
			}
		}
	}

	/**
	 * 读取全部配置项
	 * @return Properties
	 */
	public static function getProperties(): Properties
	{
		return self::$properties;
	}

	/**
	 * @return bool
	 */
	public static function isDebug(): bool
	{
		return self::$debug;
	}

	/**
	 * @return string
	 */
	public static function getDefaultModule(): string
	{
		return self::$defaultModule;
	}

	/**
	 * @return string
	 */
	public static function getDefaultController(): string
	{
		return self::$defaultController;
	}

	/**
	 * @return string
	 */
	public static function getDefaultAction(): string
	{
		return self::$defaultAction;
	}

	/**
	 * @return string
	 */
	public static function getDefaultTheme(): string
	{
		return self::$defaultTheme;
	}

	/**
	 * @return string
	 */
	public static function getUrlRoot(): string
	{
		return self::$urlRoot;
	}

	/**
	 * @return array
	 */
	public static function getDevelopers(): array
	{
		return self::$developers;
	}

	/**
	 * @return string
	 */
	public static function getFileUrlPrefix(): string
	{
		return self::$fileUrlPrefix;
	}

	public static function getUrlPrefix(): string
	{
		return self::$urlPrefix;
	}

	/**
	 * @return string
	 */
	public static function getFileSavePath(): string
	{
		return self::$fileSavePath;
	}

	/**
	 * @return string
	 */
	public static function getProjectPackage(): string
	{
		return self::$projectPackage;
	}

	/**
	 * @return string
	 */
	public static function getDefaultCharset(): string
	{
		return self::$defaultCharset;
	}

	/**
	 * @return int
	 */
	public static function getDefaultPageSize(): int
	{
		return self::$defaultPageSize;
	}

	/**
	 * @return string
	 */
	public static function getDefaultLocale(): string
	{
		return self::$defaultLocale;
	}

	/**
	 * @return string
	 */
	public static function getPasswordSalt(): string
	{
		return self::$passwordSalt;
	}

	/**
	 * @return string
	 */
	public static function getAuthType(): string
	{
		return self::$authType;
	}

	/**
	 * @return BaseDb
	 */
	public static function getDatabase(): BaseDb
	{
		return self::$database;
	}

	/**
	 * @return IApplication
	 */
	public static function getApplication(): IApplication
	{
		return self::$application;
	}

	/**
	 * @return ILogger
	 */
	public static function getLogger(): ILogger
	{
		return self::$logger;
	}

	/**
	 * @return ICache
	 */
	public static function getCache(): ICache
	{
		return self::$cache;
	}

	/**
	 * @return null
	 */
	public static function getPostRawJSON()
	{
		return self::$postRawJSON;
	}

	public static function init(array $settings = null)
	{
		set_exception_handler(["link\hefang\mvc\Mvc", "exceptionHandler"]);
		set_error_handler(["link\hefang\mvc\Mvc", "errorHandler"]);
		self::initStaticVars();
		self::initProperties($settings);
		self::initCaches();
		self::initLogger();
//        self::printSystemInfo();
		self::initUrlPrefix();
		self::initProject();
		self::initDatabase();
		self::initApplication();
		self::initControllers();
	}

	private static function initStaticVars()
	{
		self::$properties = new Properties();
		self::$application = new SimpleApplication();
		self::$logger = new SimpleFileLogger(LogLevel::warn());
		self::$cache = new SimpleCache(PATH_CACHES);

		self::$globalConfig = ObjectHelper::nullOrDefault(self::$application->onInit(), []);
	}

	private static function initProperties(array $settings = null)
	{
		$propertiesPath = PATH_APPLICATION . DIRECTORY_SEPARATOR . "application.config.php";
		if (!is_array($settings)) {
			$settings = is_file($propertiesPath) ? @include($propertiesPath) : [];
		}
		$defaultSettings = include(PHP_MVC_ROOT . DS . 'application.config.php');

		self::$settings = array_merge($defaultSettings, $settings);
		self::$logger->debug("配置项", print_r(self::$settings, true));
	}

	private static function initCaches()
	{
		$cacheClass = self::getProperty("cache.class");
		$cacheOption = self::getProperty("cache.option");
		if (StringHelper::isNullOrBlank($cacheClass)) {
			return;
		}
		$cacheClass = self::_class($cacheClass);
		if (!class_exists($cacheClass, true)) {
			self::$logger->warn("缓存类不存在", $cacheClass);
			return;
		}
		$cache = new $cacheClass($cacheOption);
		if (!($cache instanceof ICache)) {
			self::$logger->error("缓存类应为" . ICache::class . "的实现类", $cacheClass);
			return;
		} else {
			self::$cache = $cache;
		}
	}

	/**
	 * 读取配置文件中的配置项
	 * @param string $key
	 * @param null|string|array|bool|int|float $defaultValue
	 * @return null|string|array|bool|int|float
	 */
	public static function getProperty(string $key, $defaultValue = null)
	{
//        return self::$properties->getProperty($key, $defaultValue);
		return CollectionHelper::getOrDefault(self::$settings, $key, $defaultValue);
	}

	private static function initLogger()
	{
		$loggerClass = self::_class(self::getProperty("logger.class"));
		$logLevel = self::getProperty("logger.level", "WARN");
		if (StringHelper::isNullOrBlank($loggerClass)) {
			return;
		}

		if (!class_exists($loggerClass, true)) {
			return;
		}

		$logger = new $loggerClass(LogLevel::valueOf($logLevel));
		if ($logger instanceof ILogger) {
			self::$logger = $logger;
		} else {
			self::$logger->error("日志类应为'ILogger'的实现类", $loggerClass);
		}
	}

	private static function initUrlPrefix()
	{
		self::$urlPrefix = self::getProperty('prefix.url.main');
		self::$fileUrlPrefix = self::getProperty('prefix.url.file');
	}

	private static function initProject()
	{
		self::$authType = strtoupper(self::getProperty("project.auth.type", "SESSION"));
		self::$pathInfoType = strtoupper(self::getProperty("pathinfo.type", "PATH_INFO"));
		self::$debug = ParseHelper::parseBoolean(self::getProperty("debug.enable", false));
		self::$projectPackage = self::getProperty("project.package", self::$projectPackage);
		self::$passwordSalt = self::getProperty('password.salt', self::$passwordSalt);
		self::$defaultModule = self::getProperty("default.module", self::$defaultModule);
		self::$defaultController = self::getProperty("default.controller", self::$defaultController);
		self::$defaultAction = self::getProperty("default.action", self::$defaultAction);
		self::$defaultPageSize = intval(self::getProperty("default.page.size", self::$defaultPageSize));
		self::$defaultCharset = self::getProperty("default.charset", self::$defaultCharset);
		self::$defaultTheme = self::getProperty("default.theme", self::$defaultTheme);
		self::$defaultLocale = self::getProperty("default.locale", self::$defaultLocale);
		$router = self::getProperty("project.router", "auto");
		if (StringHelper::isNullOrBlank(self::$projectPackage)) {
			self::$logger->error("项目主包未设置", "项目将无法运行");
			exit("项目主包未设置");
		}

		self::$projectPackage = self::_class(self::$projectPackage);

		if (!in_array(self::$pathInfoType, ["PATH_INFO", "QUERY_STRING"])) {
			self::$pathInfoType = "PATH_INFO";
		}

		if (!in_array(self::$authType, ['TOKEN', 'SESSION', 'BOTH'])) {
			self::$authType = 'SESSION';
		}
		if (is_string($router) && strcasecmp($router, "auto") === 0) {
			$router = "auto";
		}
		if (is_array($router)) {
			//todo: 路由解析未完成
		}
		if (self::$authType === "SESSION") {
			session_name("PHP_MVC_SESSION_ID");
			session_start();
		}
	}

	private static function initDatabase()
	{
		$dbEnable = ParseHelper::parseBoolean(self::getProperty("database.enable"));
		if (!$dbEnable) {
			self::$logger->log("数据库功能未启用", "将无法进行数据库操作");
			return;
		}
		$dbClassName = self::_class(self::getProperty("database.class"));

		if (!class_exists($dbClassName, true)) {
			throw new DbException("数据库类不存在, 数据库功能已禁用");
		}
		$dbHost = self::getProperty("database.host");
		$dbUsername = self::getProperty("database.username");
		$dbPassword = self::getProperty("database.password");
		$dbDatabase = self::getProperty("database.database");
		$dbPort = intval(self::getProperty("database.port", '-1'));
		$dbCharset = self::getProperty("database.charset");
		self::$tablePrefix = self::getProperty("database.table.prefix", self::$tablePrefix);
		$db = new $dbClassName($dbHost, $dbUsername, $dbPassword, $dbDatabase, $dbCharset, $dbPort);
		if ($db instanceof BaseDb) {
			self::$database = $db;
		} else {
			Mvc::getLogger()->error("数据库类就是BaseDb的直接或间接子类");
		}
	}

	private static function initApplication()
	{
		$appClass = self::getProperty("project.application.class");
		if (StringHelper::isNullOrBlank($appClass)) {
			$appClass = self::$projectPackage . "\\Application";
		}
		$appClass = self::_class($appClass);
		if (!class_exists($appClass, true)) {
			self::$logger->error("应用程序类不存在", $appClass);
			return;
		}
		$app = new $appClass();
		if ($app instanceof IApplication) {
			self::$application = $app;
			$config = self::$application->onInit();
			is_array($config) and self::$globalConfig = $config;
		} else {
			self::$logger->error("应用程序类应为'IApplication'的实现类", $appClass);
		}
	}

	private static function initControllers()
	{
		$controllerCacheFile = PATH_CACHES . DIRECTORY_SEPARATOR . "php_mvc_controllers.cache.php";
		if (file_exists($controllerCacheFile) && !self::$debug) {
			$controllers = @include $controllerCacheFile;
			self::$controllers = $controllers["controllers"];
			self::$actions = $controllers["actions"];
			if (!empty(self::$controllers) && !empty(self::$actions)) return;
		}
		$pkgs = explode(",", self::getProperty("ext.controller.package", ""));
		$pkgs = array_filter($pkgs, "\link\hefang\helpers\StringHelper::isNullOrBlank", ARRAY_FILTER_USE_BOTH);
		$pkgs[] = self::$projectPackage;
		$pkgs = array_map("self::_class", $pkgs);
		self::$logger->notice('正在从' . count($pkgs) . "个包中读取控制器", join("\n", $pkgs));
		$classes = [];

		foreach (ClassHelper::getClassPaths() as $classPath) {
			$classes = array_merge($classes, ClassHelper::findClassesIn($classPath));
		}
		self::addControllers($classes);
		$phpString = CollectionHelper::stringify(["controllers" => self::$controllers, "actions" => self::$actions]);
		$cache = <<<CONTROLLERS
        <?php return $phpString; ?>
CONTROLLERS;
		file_put_contents($controllerCacheFile, trim($cache));
	}

	/**
	 * @param string[] $classes
	 * @param string $module
	 */
	public static function addControllers(array $classes, string $module = null)
	{
		foreach ($classes as $class) {
			if (!StringHelper::endsWith($class, false, "Controller")) continue;
			try {
				$reflection = new ReflectionClass($class);
				if (
					!$reflection->isSubclassOf(BaseController::class)
					|| $reflection->isAbstract()
					|| !$class::isController()
				) continue;
				$realModule = $module ?: $class::module();
				$controller = $class::name();
				$ck = strtolower("$realModule/$controller");
				$doc = ActionDocComment::parse($reflection->getDocComment());
				self::$controllers[$ck] = [
					"class" => $reflection->getName(),
					"doc" => $doc->toMap()
				];
				self::initActions($reflection, $ck);
			} catch (ReflectionException $e) {
			}
		}
	}

	private static function initActions(ReflectionClass $controllerClass, string $controllerKey)
	{
		$methods = $controllerClass->getMethods();
		foreach ($methods as $method) {
			if (!$method->isPublic()
				|| $method->isAbstract()
				|| $method->isConstructor()
				|| $method->isDestructor()
				|| $method->isStatic()
				|| $method->isInternal()
				|| $method->class === BaseController::class) continue;
			$returnType = $method->getReturnType();
			if (!$returnType) continue;

			if (version_compare(PHP_VERSION, "7.1.0", ">=")) {
				$returnTypeName = $returnType->getName();
			} else {
				$returnTypeName = $returnType . '';
			}

			if ($returnTypeName === BaseView::class) {
				$doc = ActionDocComment::parse($method->getDocComment());
				$actionName = $doc->getName();
				if (StringHelper::isNullOrBlank($actionName)) {
					$actionName = $method->getName();
				}
				self::$actions[strtolower("$controllerKey/$actionName")] = [
					"class" => $controllerClass->getName(),
					"method" => $method->getName(),
					"doc" => $doc->toMap()
				];
			}
		}
	}

	/**
	 * 获取表前缀
	 * @return string
	 */
	public static function getTablePrefix()
	{
		return self::$tablePrefix;
	}

	public static function systemInfo()
	{
		$startTime = TimeHelper::formatMillis();
		$os = PHP_OS;
		$phpVersion = PHP_VERSION;
		$mvcVersion = PHP_MVC;
		$pathRoot = PATH_ROOT;
		$pathApplication = PATH_APPLICATION;
		$pathThemes = PATH_THEMES;
		$pathLibraries = PATH_LIBRARIES;
		return <<<INFO
-----------------------------[ java-mvc ]------------------------------

-----------------------------[ 系统信息 ]------------------------------

- 启动时间: $startTime
- 操作系统: $os
- 服务器IP: {$_SERVER["HTTP_HOST"]} 
- PHP版本: $phpVersion 
- 框架版本: $mvcVersion
- 作    者: 何方
- 教程地址: https://hefang.link
- 项目目录: $pathRoot
- 应用目录: $pathApplication
- 库目录:  $pathLibraries
- 主题目录: $pathThemes

-----------------------------[ 系统信息 ]------------------------------
INFO;
	}

	public function start()
	{
		ob_start();
		if (self::$pathInfoType === "QUERY_STRING") {
			$qsKey = Mvc::getProperty("project.pathinfo.querystring.key", "_");
			$path = CollectionHelper::getOrDefault($_GET, $qsKey, "/");
		} else {
			$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (
			isset($_SERVER["ORIG_PATH_INFO"]) ? $_SERVER["ORIG_PATH_INFO"] : (
			isset($_SERVER['REDIRECT_PATH_INFO']) ? $_SERVER["REDIRECT_PATH_INFO"] : '/'));
		}
		$path = urldecode($path);
		/**
		 * @var BaseView
		 */
		$view = null;
		$router = self::$application->onRequest($path);
		if (!($router instanceof Router)) $router = null;
		if ($router !== null) {
			$view = $this->handleRequest($router);
		}
		$router = Router::parsePath($path);
		if ($view == null) {
			$view = $this->handleThemeStaticResources($router, $path);
		}
		if ($view == null) {
			$view = $this->handleRequest($router);
		}

		($view == null ? new TextView("") : $view)->compile()->render();
	}

	/**
	 * @param Router $router
	 * @return BaseView|null
	 */
	private function handleRequest(Router $router)
	{
		$ck = StringHelper::contact($router->getModule(), "/", $router->getController());
		$ak = StringHelper::contact($ck, "/", $router->getAction());

		if (!array_key_exists($ck, self::$controllers)) {
			throw new ControllerNotFoundException($router);
		}

		if (!array_key_exists($ak, self::$actions)) {
			throw new ActionNotFoundException($router);
		}

		$ctrlInfo = self::$controllers[$ck];
		$ctrlDoc = $ctrlInfo["doc"];

		$actMtdInfo = self::$actions[$ak];

		$controller = self::_controller($ctrlInfo["class"]);

		if (!empty($ctrlDoc["method"]) && !in_array($controller->_method(), $ctrlDoc["method"])) {
			return $controller->_restApiMethodNotAllowed();
		}

		$controller->setRouter($router);

		$type = $controller->_header("Content-Type");
		try {
			$post = null;
			if (StringHelper::contains($type, true, "json")) {
				self::$postRawJSON = @file_get_contents("php://input");
				$post = @json_decode(self::$postRawJSON, true);
				$postField = ObjectHelper::getProperty($ctrlInfo["class"], "___post");
				if ($postField) {
					$postField->setAccessible(true);
					$postField->setValue($controller, is_array($post) ? array_merge($post, $_POST) : $_POST);
				}
			}

			$requestField = ObjectHelper::getProperty($ctrlInfo["class"], "___request");

			if ($requestField) {
				$requestField->setAccessible(true);
				$requestField->setValue($controller, is_array($post) ? array_merge($_REQUEST, $post) : $_REQUEST);
			}
		} catch (Throwable $e) {
			Mvc::getLogger()->error("设置post和request值时出现异常", get_called_class(), $e);
		}
		$actionMethod = $actMtdInfo["method"];
		$actDoc = $actMtdInfo["doc"];
		if (!empty($actDoc["method"]) && !in_array($controller->_method(), $actDoc["method"])) {
			$exception = new MethodNotAllowException("不支持'{$controller->_method()}'请求方法");
			throw $exception->setRouter($router);
		}
		$view = $controller->$actionMethod($router->getCmd());
		return ($view instanceof BaseView) ? $view : null;
	}

	private static function _controller(string $class): BaseController
	{
		return new $class();
	}

	/**
	 * @param Router $router
	 * @param string $path
	 * @return null|BaseView
	 */
	private function handleThemeStaticResources(Router $router, string $path)
	{
		if (!StringHelper::startsWith($path, true, "/theme/" . $router->getTheme() . "/") ||
			StringHelper::endsWith($path, true, ".php", ".inc")) return null;
		$file = str_replace("/theme/", PATH_THEMES . DIRECTORY_SEPARATOR, $path);
		$file = str_replace('/', DIRECTORY_SEPARATOR, $file);
		return new FileView($file);
	}

	/**
	 * 将以.分割的命名空间和类转换为\分割
	 * @param string $namespace
	 * @return string
	 */
	public static function _class($namespace)
	{
		if (StringHelper::isNullOrBlank($namespace)) return $namespace;
		return str_replace(".", "\\", $namespace);
	}
}
