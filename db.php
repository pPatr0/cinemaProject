<?php
$config = require __DIR__ . '/config.php';
$c = $config['db'];
$db = mysqli_connect($c['host'], $c['user'], $c['pass'], $c['dbname']);
if (!$db) die('Connection failed: ' . mysqli_connect_error());
mysqli_set_charset($db, $c['charset']);
?>