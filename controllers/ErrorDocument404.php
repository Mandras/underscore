<?php

_::$title = "404 - Not Found";

_::$description = "404 - Not Found";

_::$html = "ErrorDocument404.html";

if (isset($_SERVER["REQUEST_URI"])) $parameters_uri = substr(stristr($_SERVER["REQUEST_URI"], '?', false), 1);
else $parameters_uri = '';

ob_start();

echo("<h5>Script URL</h5>");
if (isset(_::$route[0]))				var_dump(_::$route[0]);					 	else var_dump(NULL);

echo("<h5>Param&egrave;tres</h5>");
if (!empty($parameters_uri))			var_dump(explode('&', $parameters_uri));	else var_dump(NULL);

echo("<h5>HTTP Referer</h5>");
if (isset($_SERVER["HTTP_REFERER"]))	var_dump($_SERVER["HTTP_REFERER"]);			else var_dump(NULL);

echo("<h5>Client IP</h5>");
if (isset($_SERVER["REMOTE_ADDR"]))		var_dump($_SERVER["REMOTE_ADDR"]);			else var_dump(NULL);

_::assign('404Details', ob_get_clean());

ob_end_flush();

?>