<?php /** @var Exception $exception
 * @var mixed $code
 */ ?>
<!doctype html>
<html lang="en">
<head>
    <title>Error <?= $code ?></title>
</head>
<body>
<div style="font-family: 'Segoe UI', sans-serif;padding:50px;">
    <h1>Error&nbsp;<?= $code ?></h1>
    <p style="color:darkred"><?= $exception->getMessage() ?></p>
    <hr>
    <small style="font-size: 70%;font-weight:bold;">Powered by&nbsp; <a href="https://github.com/omidgfx/OlivePHP">OlivePHP</a></small>
</div>
</body>
</html>
