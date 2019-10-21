<?php /** @noinspection ALL */
defined("PHP_MVC_ROOT") or define("PHP_MVC_ROOT", __DIR__);
define("PHP_MVC", true);
define("PHP_MVC_VERSION", "1.0.1");
define("DS", DIRECTORY_SEPARATOR);

defined("PATH_ROOT") or define("PATH_ROOT", $_SERVER["DOCUMENT_ROOT"]);
defined("PATH_APPLICATION") or define("PATH_APPLICATION", PATH_ROOT . DIRECTORY_SEPARATOR . "application");
defined("PATH_LIBRARIES") or define("PATH_LIBRARIES", PATH_ROOT . "/libraries");
defined("PATH_THEMES") or define("PATH_THEMES", PATH_ROOT . DIRECTORY_SEPARATOR . "themes");
defined("PATH_DATA") or define("PATH_DATA", PATH_ROOT . DIRECTORY_SEPARATOR . "data");
defined("PATH_LOGS") or define("PATH_LOGS", PATH_DATA . DIRECTORY_SEPARATOR . "logs");

if (!defined("PATH_CACHES")) {
    $cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "caches";
    if (!is_writeable($cachePath)) {
        $cachePath = PATH_DATA . DIRECTORY_SEPARATOR . "caches";
    }
    define("PATH_CACHES", $cachePath);
}


version_compare(PHP_VERSION, "7.0.0", ">=") or die(
    "需要PHP版本大于7.0才能运行该框架, 当前PHP版本为: " . PHP_VERSION . PHP_EOL
);


require "link/hefang/mvc/Mvc.class.php";

\link\hefang\helpers\ClassHelper::loader(PHP_MVC_ROOT, PATH_APPLICATION);

function startMvcApplication(string $propertiesFile = null)
{
    \link\hefang\mvc\Mvc::init($propertiesFile);
    $mvc = new \link\hefang\mvc\Mvc();
    $mvc->start();
}