<?php

define('PROD', 	0);
define('DEV', 	1);
define('UNDFN', 2);

if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));

require_once(__DIR__ . "/framework/underscore.php");

new _();

?>
