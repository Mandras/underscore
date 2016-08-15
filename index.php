<?php

define('PROD', 	0);
define('DEV', 	1);
define('UNDFN', 2);

if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));

ini_set('error_log', __DIR__ . '/logs/error.' . date("Y-m-d") . '.log');

require_once(__DIR__ . "/framework/underscore.php");

new _();

?>
