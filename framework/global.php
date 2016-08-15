<?php

// Permet de charger automatiquement le model tools.php
require_once(__DIR__ . '/../models/tools.php');

// Pour se connecter automatiquement a MYSQL
// mysql::connect();

// Pour utiliser la session automatiquement
// session_start();

// Pour forcer une langue particuliere (par defaut = detection de la langue du navigateur)
// _::$language = 'en';

// Pour ajouter une meta dans le <head>
// _::$metas["appid"] = "0123456789";

// Ici definir la liste (ARRAY) des elements JS & CSS a charger pour toutes les pages
// Variables possible:
	# $js 			- Pour charger un fichier JS dans le <head>
	# $js_async 	- Pour charger un fichier JS en asynchrone dans le <body>
	# $css 			- Pour charger un fichier CSS dans le <head>
	# #css_async 	- Pour charger un fichier CSS en asynchrone dans le <body>

// JQuery est deja chargé dans le layout.html (en fin de page)

_::$js_async []= "bootstrap.min.js";
_::$js_async []= "ie10-viewport-bug-workaround.js";
_::$js_async []= "jquery.autosizer.js";
_::$js_async []= "jquery.cookie.js";
_::$js_async []= "jquery.url.js";
_::$js_async []= "script.js";

_::$css []= "bootstrap.min.css";
_::$css []= "ie10-viewport-bug-workaround.css";
_::$css []= "style.css";

// --

?>