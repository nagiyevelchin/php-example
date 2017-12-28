<?php
/**
 * 9/29/15 2:53 PM
 */
//$begin = microtime();
header('Content-type: text/html; charset=utf-8');
require_once("System/Classes.php");

$config = './Options/Config.server.user.php';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $config = './Options/Config.local.user.php';
}
new NE\System\Elgroup($config);
//$end = microtime();
//print '<div style="display:none;">'.($end-begin).'</div>';
