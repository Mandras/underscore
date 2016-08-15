<?php

_::$html = "guide.html";

_::$title = "Underscore - Guide";

_::$description = "Underscore, PHP 7 Light Framework";

if (isset(_::$route[1]) && _::$route[1] == "demo") {
	echo("<code style='background: #555;color: #FFF;padding: 4px 10px;'>var_dump(_::&#36;route);</code><br /><br />");
	var_dump(_::$route);

	exit(0);
}

if (_::$is_alias) _::assign('aliased', 1);
else _::assign('aliased', 0);

if (_::$siteurl == 'http://rbenoit.fr') _::assign('print_download', 1);
else _::assign('print_download', 0);

_::assign('planet', 'World');

_::$metas['appid'] = '161273083968709';

?>