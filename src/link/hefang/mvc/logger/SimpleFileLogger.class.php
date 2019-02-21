<?php

namespace link\hefang\mvc\logger;


use link\hefang\enums\LogLevel;
use link\hefang\interfaces\ILogger;
use link\hefang\mvc\Mvc;

class SimpleFileLogger implements ILogger
{
    private $level;
    private static $useConsole = false;

    /**
     * SimpleFileLogger constructor.
     * @param LogLevel $level
     */
    public function __construct(LogLevel $level = null)
    {
        $this->level = $level === null ? LogLevel::warn() : $level;
    }

    public function getLevel(): LogLevel
    {
        return $this->level;
    }

    public function setLevel(LogLevel $level)
    {
        $this->level = $level;
    }

    public function log(string $name, string $content)
    {
        if ($this->level->getValue() == LogLevel::NONE && !Mvc::isDebug()) return;
        self::write(strtoupper(__FUNCTION__), $name, $content);
    }

    public function notice(string $name, string $content)
    {
        if ($this->level->getValue() == LogLevel::NOTICE && !Mvc::isDebug()) return;
        self::write(strtoupper(__FUNCTION__), $name, $content);
    }

    public function warn(string $name, string $content, \Throwable $e = null)
    {
        if ($this->level->getValue() == LogLevel::WARN && !Mvc::isDebug()) return;
        self::write(strtoupper(__FUNCTION__), $name, $content, $e);
    }

    public function error(string $name, string $content, \Throwable $e = null)
    {
        if ($this->level->getValue() == LogLevel::ERROR && !Mvc::isDebug()) return;
        self::write(strtoupper(__FUNCTION__), $name, $content, $e);
    }

    public function debug(string $name, string $content)
    {
        if (!Mvc::isDebug()) return;
        self::write(strtoupper(__FUNCTION__), $name, $content);
    }

    private static function write(string $name, string $title, string $content, \Throwable $exception = null)
    {
        $time = date("Y-m-d H:i:s");
        $dir = PATH_LOGS . DS . date("Y-m") . DS;
        if (!self::$useConsole && !is_dir($dir) && !mkdir($dir, 0770, true)) {
            self::$useConsole = true;
        }

        $file = $dir . date("d") . ".log";
        $content = "[$time]($name) $title\n$content\n";
        if ($exception !== null) {
            $content .= <<<CONTENT
            文件: {$exception->getFile()}({$exception->getLine()})
            信息: {$exception->getMessage()}
            堆栈: {$exception->getTraceAsString()}
CONTENT;
        }
        if (self::$useConsole) {
            error_log($content);
        } else {
            file_put_contents($file, $content . "\n", FILE_APPEND);
        }
    }

    static function init()
    {
        if (!is_writable(PATH_LOGS)) {
            self::$useConsole = true;
            self::write("ERROR", "日志目录不可写, 已自动改为在html中输出注释", PATH_LOGS);
        }
    }
}

SimpleFileLogger::init();