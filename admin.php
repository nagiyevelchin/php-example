<?php
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_STRICT);
header('Content-type: text/html; charset=utf-8');
require_once("System/Classes.php");

$config = './Options/Config.server.admin.php';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $config = './Options/Config.local.admin.php';
}
new NE\System\Elgroup($config);
?>