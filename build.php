<?php
$name = "php-mvc";

$web = "$name.php";
$cli = "$name.php";

$version = '0.0.2';
//$version = "SNAPSHOT";

$fnname = __DIR__ . "/build/$name-$version.phar";
//$fnname = "/Volumes/DATASWAP/DevDir/coya-paper-main/libraries/php-mvc-$version.phar";

$flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME;

$indexFile = __DIR__ . "/src/" . $web;
$indexContent = file_get_contents(__DIR__ . "/php-mvc-templeta.php");
$indexContent = str_replace("!!VERSION!!", $version, $indexContent);


file_put_contents($indexFile, $indexContent);


$phar = new Phar($fnname, $flags, "$name.phar");

$phar->buildFromDirectory(__DIR__ . "/src");

$stub = str_replace(
    "ExtractPhar",
    "ExtractPhar" . md5(microtime() . rand(PHP_INT_MIN, PHP_INT_MAX)),
    file_get_contents("stub_templete.php")
);
$stub = str_replace("!!WEB_ENTRY!!", $web, $stub);
$stub = str_replace("!!CLI_ENTRY!!", $cli, $stub);
$phar->setStub($stub);

$phar->compressFiles(Phar::GZ);