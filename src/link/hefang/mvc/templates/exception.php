<?php
if (!isset($exception) || !($exception instanceof Throwable)) return;


use link\hefang\mvc\exceptions\ModelException;
use link\hefang\mvc\exceptions\SqlException;
use link\hefang\mvc\helpers\DebugHelper;

$debug = DebugHelper::debugInfo();
?>

<!doctype html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
			content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="chrome=1,firefox=1,edge=1,ie=edge,ie=11,ie=10">
	<title>出现异常 - HeFangMVC</title>
	<style>
		table {
			border-collapse: collapse;
		}

		th, td {
			border: thin solid gray;
			padding: 5px;
		}

		th {
			text-align: right;
			min-width: 150px;
		}

		th:after {
			content: ":";
		}

		p {
			margin: 8px 0;
		}
	</style>
</head>
<body>
<h1>
	HeFangMVC
	<?php if ($exception instanceof ModelException) { ?>
		解析数据模型时出现未处理的异常
	<?php } elseif ($exception instanceof SqlException) { ?>
		和数据库交互时出现未处理的异常
	<?php } elseif ($exception instanceof RuntimeException) { ?>
		出现了一个未处理的运行时异常
	<?php } else { ?>
		出现了一个未处理的异常
	<?php } ?>
</h1>
<h2><?= $exception->getMessage() ?></h2>
<p>文件：<?= $exception->getFile() ?></p>
<p>第<?= $exception->getLine() ?>行</p>
<h2>调用信息：</h2>
<ol>
	<?php foreach ($exception->getTrace() as $trace) { ?>
		<li>
			<?= $trace["file"] ?>
			<p>第<?= $trace["line"] ?>行， 函数: <?= $trace["function"] ?></p>
		</li>
	<?php } ?>
</ol>
<h2>服务器环境信息</h2>
<table>
	<tbody>
	<tr>
		<th>地址</th>
		<td><?= $debug["serverHost"] ?></td>
	</tr>
	<tr>
		<th>名称</th>
		<td><?= $debug["serverName"] ?></td>
	</tr>
	<tr>
		<th>操作系统</th>
		<td><?= $debug["serverOS"] ?></td>
	</tr>
	<tr>
		<th>PHP版本</th>
		<td><?= PHP_VERSION ?></td>
	</tr>
	<tr>
		<th>PHP解释器路径</th>
		<td><?= PHP_BINARY ?></td>
	</tr>
	<tr>
		<th>HeFangMVC版本</th>
		<td><?= PHP_MVC ?></td>
	</tr>
	<tr>
		<th>加载的PHP扩展</th>
		<td>
			<?php foreach ($debug["loadedExtensions"] as $extension) { ?>
				<a target="_blank"
					href="https://www.php.net/manual-lookup.php?pattern=<?= $extension ?>&scope=quickref"><?= $extension ?></a>,
			<?php } ?>
		</td>
	</tr>
	</tbody>
</table>
<?php if ($sqlCount = count($debug["executedSQL"]) > 0) { ?>
	<h2>当前请求执行的SQL语句(<?= $sqlCount ?>)</h2>
	<ol>
		<?php foreach ($debug["executedSQL"] as $sql) { ?>
			<li><?= str_replace("\n参数", "<br/>参数", $sql) ?></li>
		<?php } ?>
	</ol>
<?php } ?>
<?php if ($pluginCount = count($debug["loadedPlugins"]) > 0) { ?>
	<h2>当前请求加载的插件(<?= $pluginCount ?>)</h2>
	<ol>
		<?php foreach ($debug["loadedPlugins"] as $plugin) { ?>
			<li><?= json_encode($plugin, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></li>
		<?php } ?>
	</ol>
<?php } ?>
</body>
</html>
