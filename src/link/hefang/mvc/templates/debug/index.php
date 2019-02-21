<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge,chrome=1">
    <title>出现异常</title>
</head>
<body>
<pre>
    <?= ob_get_clean() ?>
    <?php var_dump($exception); ?>
</pre>
</body>
</html>