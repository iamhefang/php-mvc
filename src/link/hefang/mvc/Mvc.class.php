<?php

namespace link\hefang\mvc;
defined('PHP_MVC') or die("Access Refused");

use link\hefang\entities\Properties;
use link\hefang\enums\LogLevel;
use link\hefang\exceptions\IOException;
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
use link\hefang\mvc\entities\Router;
use link\hefang\mvc\exceptions\ActionNotFoundException;
use link\hefang\mvc\exceptions\ControllerNotFoundException;
use link\hefang\mvc\exceptions\PhpErrorException;
use link\hefang\mvc\interfaces\IApplication;
use link\hefang\mvc\logger\SimpleFileLogger;
use link\hefang\mvc\views\BaseView;
use link\hefang\mvc\views\FileView;
use link\hefang\mvc\views\TextView;
use ReflectionClass;
use ReflectionException;
use Throwable;

class Mvc
{
	const VERSION = PHP_MVC;

	private static $pathInfoType = "PATH_INFO";

	private static $rawPostData = null;

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
	private static $defaultPageSize = 20;
	private static $defaultTheme = "default";
	private static $defaultLocale = "zh_CN";

	private static $passwordSalt = "";

	private static $authType = "SESSION";

	/**
	 * @var BaseDb
	 */
	private static $database;

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
	 * 读取配置文件中的配置项
	 * @param string $key
	 * @param string|null $defaultValue
	 * @return null|string
	 */
	public static function getProperty(string $key, string $defaultValue = null)
	{
//        return self::$properties->getProperty($key, $defaultValue);
		return CollectionHelper::getOrDefault(self::$settings, $key, $defaultValue);
	}

	/**
	 * 读取全局配置项
	 * @param string $key
	 * @param mixed|null $defaultValue
	 * @return mixed|null
	 */
	public static function getConfig(string $key, $defaultValue = null)
	{
		return CollectionHelper::getOrDefault(self::$globalConfig, $key, $defaultValue);
	}

	public static function putConfig(array $config)
	{
		self::$globalConfig = $config;
	}

	public static function addConfig(string $name, $value)
	{
		self::$globalConfig[$name] = $value;
	}

	public static function exceptionHandler(Throwable $exception)
	{
		self::$logger and self::$logger->error($exception->getMessage(), $exception->getMessage(), $exception);
		if (self::$application != null) {
			$view = self::$application->onException($exception);
			if ($view !== null) {
				$view->compile()->render();
			} else {
				include "templates/debug/index.php";
				exit(0);
			}
		}
	}

	public static function errorHandler(int $errno, string $errstr, string $errfile = null, int $errline = -1)
	{
		$e = new PhpErrorException($errno, $errstr, $errfile, $errline);
		self::exceptionHandler($e);
	}

	/**
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
	public static function getRawPostData()
	{
		return self::$rawPostData;
	}

	public static function init(string $propertiesPath = null)
	{
		set_exception_handler(["link\hefang\mvc\Mvc", "exceptionHandler"]);
		set_error_handler(["link\hefang\mvc\Mvc", "errorHandler"]);
		self::initStaticVars();
		self::initProperties($propertiesPath);
		self::initCaches();
		self::initLogger();
//        self::printSystemInfo();
		self::initUrlPrefix();
		self::initProject();
		self::initDatabase();
		self::initApplication();
		self::initControllers();
	}

	private static function initUrlPrefix()
	{
		self::$urlPrefix = self::getProperty('prefix.url.main');
		self::$fileUrlPrefix = self::getProperty('prefix.url.file');
	}

	private static function initStaticVars()
	{
		self::$properties = new Properties();
		self::$application = new SimpleApplication();
		self::$logger = new SimpleFileLogger(LogLevel::warn());
		self::$cache = new SimpleCache(PATH_CACHES);

		self::$globalConfig = ObjectHelper::nullOrDefault(self::$application->onInit(), []);
	}

	private static function initProperties(string $propertiesPath = null)
	{
		if (StringHelper::isNullOrBlank($propertiesPath)) {
			$propertiesPath = PATH_APPLICATION . DIRECTORY_SEPARATOR . "application.config.php";
			if (!file_exists($propertiesPath)) {
				$propertiesPath = PATH_APPLICATION . DIRECTORY_SEPARATOR . "config.properties";
			}
		}
		$defaultSettings = include(PHP_MVC_ROOT . DS . 'application.config.php');

		if (is_file($propertiesPath)) {
//            self::$logger->notice("发现配置文件, 正在读取", $propertiesPath);
			if (StringHelper::endsWith($propertiesPath, true, '.php')) {
				self::$settings = include($propertiesPath);
			} elseif (StringHelper::endsWith($propertiesPath, true, '.properties')) {
				try {
					self::$properties->loadFile($propertiesPath);
					$settings = self::$properties->propertyNames();
					foreach ($settings as $setting) {
						self::$settings[$setting] = self::$properties->getProperty($setting);
					}
//                $properties = self::$properties->propertyNames();
//                empty($properties) ?
//                    self::$logger->warn("配置文件中无有效配置项", $propertiesPath) :
//                    self::$logger->notice("共读取到" . count($properties) . "个配置项", $propertiesPath);
				} catch (IOException $e) {
					self::$logger->error("读取配置文件异常", $e->getMessage(), $e);
				}
			} else {
				self::$logger->error("配置文件不合法", $propertiesPath);
			}
		} else {
			self::$logger->error("配置文件不存在", $propertiesPath);
		}
		self::$settings = array_merge($defaultSettings, self::$settings);
		self::$logger->debug("配置项", print_r(self::$settings, true));
	}

	private static function initLogger()
	{
		$loggerClass = self::getProperty("logger.class");
		$logLevel = self::getProperty("logger.level", "WARN");
		if (StringHelper::isNullOrBlank($loggerClass)) {
//            self::$logger->error("日志类未设置", "将使用默认日志类: " . SimpleFileLogger::class);
			return;
		}

		if (!class_exists($loggerClass, true)) {
//            self::$logger->error("日志类不存在, 将使用默认日志类", $loggerClass);
			return;
		}

		$logger = new $loggerClass(LogLevel::valueOf($logLevel));
		if ($logger instanceof ILogger) {
			self::$logger = $logger;
		} else {
			self::$logger->error("日志类应为'ILogger'的实现类", $loggerClass);
		}
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

		if (StringHelper::isNullOrBlank(self::$projectPackage)) {
			self::$logger->error("项目主包未设置", "项目将无法运行");
			exit("项目主包未设置");
		}

		self::$projectPackage = str_replace(".", "\\", self::$projectPackage);

		if (!in_array(self::$pathInfoType, ["PATH_INFO", "QUERY_STRING"])) {
			self::$pathInfoType = "PATH_INFO";
		}

		if (!in_array(self::$authType, ['TOKEN', 'SESSION', 'BOTH'])) {
			self::$authType = 'SESSION';
		}

//        self::$logger->notice("读取项目默认值", join("", [
//            " - 默认模块: ", self::$defaultModule
//            , "\n - 默认控制器: ", self::$defaultController
//            , "\n - 默认动作: ", self::$defaultAction
//            , "\n - 默认页大小: ", self::$defaultPageSize
//            , "\n - 默认编码: ", self::$defaultCharset
//            , "\n - 默认语言: ", self::$defaultLocale
//            , "\n - 默认主题: ", self::$defaultTheme
//        ]));
		session_name("PHP_MVC_SESSION_ID");
	}

	private static function initDatabase()
	{
		$dbEnable = ParseHelper::parseBoolean(self::getProperty("database.enable"));
		if (!$dbEnable) {
			self::$logger->log("数据库功能未启用", "将无法进行数据库操作");
			return;
		}
		$dbClassName = str_replace('.', "\\", self::getProperty("database.class"));

		if (!class_exists($dbClassName, true)) {
			$dbEnable = false;
			self::$logger->error("数据库类不存在, 数据库功能已禁用", $dbClassName);
			return;
		}


		$dbHost = self::getProperty("database.host");
		$dbUsername = self::getProperty("database.username");
		$dbPassword = self::getProperty("database.password");
		$dbDatabase = self::getProperty("database.database");
		$dbPort = intval(self::getProperty("database.port", '-1'));
		$dbCharset = self::getProperty("database.charset");

//        self::$logger->notice("读取数据库配置", join("", ["",
//            "\0 - 类名: ", $dbClassName,
//            "\n - 主机: ", $dbHost,
//            "\n - 端口: ", $dbPort > 0 ? $dbPort : '默认',
//            "\n - 用户名: ", $dbUsername,
//            "\n - 数据库: ", $dbDatabase,
//            "\n - 编码: ", $dbCharset
//        ]));
		try {
			$db = new $dbClassName($dbHost, $dbUsername, $dbPassword, $dbDatabase, $dbCharset, $dbPort);
			if ($db instanceof BaseDb) {
				self::$database = $db;
			}
		} catch (Throwable $e) {
			Mvc::$logger->error("初始化数据库配置异常", $e->getMessage(), $e);
		}

	}

	private static function initApplication()
	{
		$appClass = self::getProperty("project.application.class");
		if (StringHelper::isNullOrBlank($appClass)) {
			$appClass = self::$projectPackage . "\\Application";
		}
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
		$pkgs = array_map(function ($pkg) {
			return str_replace(".", "\\", $pkg);
		}, $pkgs);
		self::$logger->notice('正在从' . count($pkgs) . "个包中读取控制器", join("\n", $pkgs));
		$classes = [];

		foreach (ClassHelper::getClassPaths() as $classPath) {
			$classes = array_merge($classes, ClassHelper::findClassesIn($classPath));
		}
		foreach ($classes as $class) {
			try {
				$reflection = new ReflectionClass($class);
				if (!$reflection->isSubclassOf(BaseController::class)) continue;

				if (!$class::isController()) continue;
				$module = $class::module();
				$controller = $class::name();
				$ck = strtolower("$module/$controller");
				self::$controllers[$ck] = $class;
				self::initActions($reflection, $ck);
			} catch (ReflectionException $e) {
			}
		}
		$cs = join(",", array_map(function ($v, $k) {
			return "'$k'=>'$v'";
		}, self::$controllers, array_keys(self::$controllers)));
		$as = join(",", array_map(function ($v, $k) {
			return "'$k'=>'$v'";
		}, self::$actions, array_keys(self::$actions)));
		$cache = <<<CONTROLLERS
        <?php return ['controllers'=>[$cs],'actions'=>[$as]]; ?>
CONTROLLERS;
		file_put_contents($controllerCacheFile, trim($cache));
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
				$name = $returnType->getName();
			} else {
				$name = $returnType . '';
			}

			if ($name === BaseView::class) {
				self::$actions[strtolower("$controllerKey/$method->name")] = $method->name;
			}
		}
	}

	private static function printSystemInfo()
	{
		$startTime = TimeHelper::formatMillis();
		$os = PHP_OS;
		$phpVersion = PHP_VERSION;
		$mvcVersion = PHP_MVC;
		$pathRoot = PATH_ROOT;
		$pathApplication = PATH_APPLICATION;
		$pathThemes = PATH_THEMES;
		$pathLibraries = PATH_LIBRARIES;
		self::$logger->log(
			"应用程序已启动...", <<<INFO
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
INFO
		);
	}

	private static function initCaches()
	{
		$cacheClass = self::getProperty("cache.class");
		$cacheOption = self::getProperty("cache.option");
		if (StringHelper::isNullOrBlank($cacheClass)) {
//            self::$logger->warn("缓存类未设置", "正在使用默认缓存类: " . SimpleCache::class);
			return;
		}
		if (!class_exists($cacheClass, true)) {
			self::$logger->warn("缓存类不存在", $cacheClass);
			return;
		}
		try {
			$cache = new $cacheClass($cacheOption);
			if (!($cache instanceof ICache)) {
				self::$logger->error("缓存类应为" . ICache::class . "的实现类", $cacheClass);
				return;
			}
			self::$cache = $cache;
		} catch (Throwable $exception) {
			self::$logger->error("实例化缓存类时出现异常", $exception->getMessage(), $exception);
		}
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
//            $path = CollectionHelper::getOrDefault($_SERVER, "PATH_INFO", "/");
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

		$controllerClass = self::$controllers[$ck];
		$actionMethod = self::$actions[$ak];


		$controller = self::_controller($controllerClass);
		$controller->setRouter($router);

		try {
			self::$rawPostData = @file_get_contents("php://input");
			$post = @json_decode(self::$rawPostData, true);
			$postField = ObjectHelper::getProperty($controllerClass, "___post");
			$requestField = ObjectHelper::getProperty($controllerClass, "___request");
			if ($postField) {
				$postField->setAccessible(true);
				$postField->setValue($controller, is_array($post) ? array_merge($post, $_POST) : $_POST);
			}

			if ($requestField) {
				$requestField->setAccessible(true);
				$requestField->setValue($controller, is_array($post) ? array_merge($_REQUEST, $post) : $_REQUEST);
			}

		} catch (Throwable $e) {
		}

		$view = $controller->$actionMethod($router->getCmd());
		return ($view instanceof BaseView) ? $view : null;
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

	private static function _controller(string $class): BaseController
	{
		return new $class();
	}
}
