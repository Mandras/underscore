<?php

/*
** UNDERSCORE - http://rbenoit.fr
** v1.0
*/

require_once(__DIR__ . "/smarty/Smarty.class.php");

require_once(__DIR__ . "/../classes/error.php");
require_once(__DIR__ . "/../classes/mysql.php");

class _ {
	private static $dev_environment = array();

	private static $alias = array();
	private static $route = array();
	public static $is_alias = false;

	private static $time_start = 0;

	private static $language_block = 'language';
	private static $default_language = 'en';
	private static $languages = array();
	private static $translate = true;
	private static $language;

	protected static $environment = PROD;

	private static $mobile_detect = true;

	public static $is_desktop = 0;
	public static $is_tablet = 0;
	public static $is_mobile = 0;

	public static $robots = "";
	public static $description = "";
	public static $title = "";

	private static $viewport = "";
	private static $charset = "";
	private static $langue = "";
	private static $html = "";

	public static $metas = array();

	protected static $method = "";

	protected static $siteurl = "";
	protected static $request_uri = "";

	protected static $js_variables = array();
	protected static $js_async = array();
	protected static $js = array();

	protected static $css_async = array();
	protected static $css = array();

	private static $minifier = true;

	private static $smarty;

	public static $mysql_host = "";
	public static $mysql_username = "";
	public static $mysql_password = "";
	public static $mysql_database = "";

	public static $mail_smtp = "";
	public static $mail_port = "";
	public static $mail_username = "";
	public static $mail_password = "";

	function __construct() {

		self::$time_start = microtime(true);

		// Verification des variables d'environnement

		if (!isset($_SERVER["HTTP_USER_AGENT"])) error(500, "Variable d'environnement absente: HTTP_USER_AGENT");
		if (!isset($_SERVER["REQUEST_METHOD"]))  error(500, "Variable d'environnement absente: REQUEST_METHOD");
		if (!isset($_SERVER["REQUEST_URI"])) 	 error(500, "Variable d'environnement absente: REQUEST_URI");
		if (!isset($_SERVER["HTTP_HOST"]))   	 error(500, "Variable d'environnement absente: HTTP_HOST");

		// Detection du request_scheme

		if (!isset($_SERVER["REQUEST_SCHEME"])) {
			if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443))
				$_SERVER["REQUEST_SCHEME"] = 'https';
			else $_SERVER["REQUEST_SCHEME"] = 'http';
		}

		// Detection de la methode

		self::$method = $_SERVER["REQUEST_METHOD"];

		// Enregistrement de l'URL du site

		self::$siteurl = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["HTTP_HOST"];
		self::$request_uri = current(explode("?", $_SERVER["REQUEST_URI"]));

		// Chargement du fichier config.ini

		if (file_exists(__DIR__ . '/config.ini')) {
			$CONFIGINI = parse_ini_file(__DIR__ . '/config.ini', false, INI_SCANNER_TYPED);
			foreach ($CONFIGINI as $_K => $_V) {
				eval('self::$' . $_K . ' = ' . var_export($_V, true) . ';');
			}

			// Si la variable siteurl est modifiee, alors on le remplace dans REQUEST_URI
			if (isset($CONFIGINI["siteurl"])) {
				if (substr($CONFIGINI["siteurl"], -1) == '/')
					$CONFIGINI["siteurl"] = substr($CONFIGINI["siteurl"], 0, -1);
				if (!empty($CONFIGINI["siteurl"])) {
					$_SERVER["REQUEST_URI"] = str_replace($CONFIGINI["siteurl"], '', $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
					self::$siteurl = $CONFIGINI["siteurl"];
				}
			}

			unset($CONFIGINI);
		}
		else error(500, "Manque le fichier de configuration: config.ini");

		// Definition de la route

		$REQUEST_URI_ARRAY = explode('/', self::$request_uri);
		foreach ($REQUEST_URI_ARRAY as $value)
			if (!empty($value)) self::$route []= $value;
		unset($REQUEST_URI_ARRAY);

		// Verification d'association des alias

		if (!empty(self::$route) && in_array(self::$route[0], self::$alias)) {
			self::$route[0] = array_search(self::$route[0], self::$alias);
			self::$is_alias = true;
		}
		else if (empty(self::$route)) { self::$route = array("index"); }

		// Gestion des ajax

		if (sizeof(self::$route) >= 2 && self::$route[0] == 'ajax' && file_exists(__DIR__ . '/../ajax/' . self::$route[1] . '.php')) {
			self::log('ajax');
			require_once(__DIR__ . '/../ajax/' . self::$route[1] . '.php');
			mysql::close();
			exit(0);
		}

		// Log de la page

		self::log('access');

		// Instansiation de Smarty

		self::$smarty = new Smarty(false);

		self::$smarty->setTemplateDir(__DIR__ . '/../html');
		self::$smarty->setCompileDir(__DIR__ . '/../cache');
		self::$smarty->setCacheDir(__DIR__ . '/../cache');

		// Language detection

		if (self::$translate) {
			if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) < 2)
				$_SERVER['HTTP_ACCEPT_LANGUAGE'] = self::$default_language;
			self::$language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}

		// Device detection

		if (self::$mobile_detect) {

			require_once(__DIR__ . "/../api/mobile_detect/mobile_detect.php");

			$MOBILE_DETECTION = new Mobile_Detect;
			if ($MOBILE_DETECTION->isMobile() && !$MOBILE_DETECTION->isTablet())
				self::$is_mobile = 1;
			else if (!$MOBILE_DETECTION->isMobile() && $MOBILE_DETECTION->isTablet())
				self::$is_tablet = 1;
			else
				self::$is_desktop = 1;
			unset($MOBILE_DETECTION);
		}

		// Verification environnement de production ou developpement

		if (in_array($_SERVER["HTTP_HOST"], self::$dev_environment)) self::set_environment(DEV);

		// Remplissage de js_variables

		self::$js_variables []= array("key" => "siteurl", 		"value" => self::$siteurl);
		self::$js_variables []= array("key" => "scriptname", 	"value" => self::$route[0]);

		if (self::$translate)
			self::$js_variables []= array("key" => "language", 		"value" => self::$language);

		if (self::$mobile_detect) {
			self::$js_variables []= array("key" => "is_desktop", 	"value" => self::$is_desktop);
			self::$js_variables []= array("key" => "is_tablet", 	"value" => self::$is_tablet);
			self::$js_variables []= array("key" => "is_mobile", 	"value" => self::$is_mobile);
		}

		// On verifie si il s'agit de la page d'accueil ou d'une page introuvable

		if (!self::controller_exists(self::$route[0])) {
			require_once(__DIR__ . "/global.php");
			self::not_found();
		}

		// Appel du script global

		require_once(__DIR__ . "/global.php");

		// Definition du html

		self::$html = self::$route[0] . ".html";

		// Appel de la vue

		require_once(__DIR__ . "/../controllers/" . self::$route[0] . ".php");

		// On termine

		self::quit();
	}

	/**
	* Quitte l'execution de la page tout en affichant ce qui doit etre affiche
	* @param  $status   Le code pour exit() => 0 = tout est ok, 1 => erreur
	*
	* @access public
	*/
	public static function quit($status = 0) {

		// Verification de la presence des HTML

		if (!file_exists(__DIR__ . "/../html/" . self::$html))
			error(500, "Fichier HTML non accessible ou introuvable");

		// Gestion du cache CSS & JS

		if (self::$minifier) {
			self::cache_generator(self::$js_async, 'js');
			self::cache_generator(self::$js, 'js');
		
			self::cache_generator(self::$css_async, 'css');
			self::cache_generator(self::$css, 'css');

			self::$smarty->loadFilter("output", "strip");
		}
		else self::$smarty->loadFilter("output", "trimwhitespace");

		if (self::$environment == DEV) {

			$mt_exec_php = microtime(true) - self::$time_start - mysql::$execution_time;
			self::assign('_execution_time_php', 	number_format($mt_exec_php, 5, ',', ''));
			self::assign('_execution_time_mysql', 	number_format(mysql::$execution_time, 5, ',', ''));
			self::assign('_execution_time_total', 	number_format($mt_exec_php + mysql::$execution_time, 5, ',', ''));
			unset($mt_exec_php);
		}

		// Traduction

		if (self::$translate) {
			self::$smarty->registerPlugin('block', self::$language_block, array('_', 'translate'), true);
			if (!file_exists(__DIR__ . '/../language/' . self::$language . '/' . self::$route[0] . '.xml')) self::$language = self::$default_language;
			if (file_exists(__DIR__ . '/../language/' . self::$language . '/' . self::$route[0] . '.xml')) {
				$_tmp_language_content = simplexml_load_file(__DIR__ . '/../language/' . self::$language . '/' . self::$route[0] . '.xml');
				foreach ($_tmp_language_content as $value) {
					if (!isset($value['target'])) $value['target'] = null;
					$_tmp_lt = trim(str_replace(array("\r", "\n"), '', (string)$value));

					switch ($value['target']) {
						case 'meta':
							if (isset($value['property']))
								self::$metas[(string)$value['property']] = $_tmp_lt;
						break;
						case 'title':
							self::$title = $_tmp_lt;
						break;
						case 'description':
							self::$description = $_tmp_lt;
						break;
						default:
							self::$languages[(string)$value['id']] = $_tmp_lt;
						break;
					}

				}
				unset($_tmp_language_content);
				unset($_tmp_lt);
			}
		}

		// Assignation des variables Smarty

		self::assign('_RequestScheme', 	$_SERVER["REQUEST_SCHEME"]);
		self::assign('_ViewPort', 		self::$viewport);
		self::assign('_Description', 	self::$description);
		self::assign('_Robots', 		self::$robots);
		self::assign('_HTML', 			self::$html);
		self::assign('_Title', 			self::$title);
		self::assign('_Charset', 		self::$charset);
		self::assign('_Language', 		self::$langue);
		self::assign("_Metas", 			self::$metas);
		self::assign('_Environment', 	self::$environment);
		self::assign('_Minifier', 		self::$minifier);

		self::assign('siteurl', 		self::$siteurl);
		self::assign('scriptname', 		self::$route[0]);

		if (self::$translate)
			self::assign('language', 		self::$language);

		if (self::$mobile_detect) {
			self::assign('is_desktop', 		self::$is_desktop);
			self::assign('is_tablet', 		self::$is_tablet);
			self::assign('is_mobile', 		self::$is_mobile);
		}

		// Encodage des assets CSS & JS

		foreach (self::$css as $key => $value) {
			if (filter_var($value, FILTER_VALIDATE_URL) === false) {
				self::$css[$key] = self::$siteurl . '/css/' . $value;
				if (self::$environment == DEV && strpos($value, '?') === false && file_exists(__DIR__ . '/../css/' . $value))
					self::$css[$key] .= '?v=' . filemtime(__DIR__ . '/../css/' . $value);
			}
		}

		foreach (self::$css_async as $key => $value) {
			if (filter_var($value, FILTER_VALIDATE_URL) === false) {
				self::$css_async[$key] = self::$siteurl . '/css/' . $value;
				if (self::$environment == DEV && strpos($value, '?') === false && file_exists(__DIR__ . '/../css/' . $value))
					self::$css_async[$key] .= '?v=' . filemtime(__DIR__ . '/../css/' . $value);
			}
		}

		foreach (self::$js as $key => $value) {
			if (filter_var($value, FILTER_VALIDATE_URL) === false) {
				self::$js[$key] = self::$siteurl . '/js/' . $value;
				if (self::$environment == DEV && strpos($value, '?') === false && file_exists(__DIR__ . '/../js/' . $value))
					self::$js[$key] .= '?v=' . filemtime(__DIR__ . '/../js/' . $value);
			}
		}

		foreach (self::$js_async as $key => $value) {
			if (filter_var($value, FILTER_VALIDATE_URL) === false) {
				self::$js_async[$key] = self::$siteurl . '/js/' . $value;
				if (self::$environment == DEV && strpos($value, '?') === false && file_exists(__DIR__ . '/../js/' . $value))
					self::$js_async[$key] .= '?v=' . filemtime(__DIR__ . '/../js/' . $value);
			}
		}

		self::assign("_CSS",		self::$css);
		self::assign('_CSSAsync',	self::$css_async);
		self::assign('_JS',			self::$js);
		self::assign('_JSAsync',	self::$js_async);

		// Encodage des variables JS

		foreach (self::$js_variables as $key => $value) {
			switch (gettype($value["value"])) {
				case "boolean" 	:
					if ($value["value"]) self::$js_variables[$key]["value"] = 'true';
					else self::$js_variables[$key]["value"] = 'false';
				break;
				case "integer" 	: break;
				case "double" 	: break;
				case "string" 	:
					self::$js_variables[$key]["value"] = "'" . str_replace("'", "\'", $value["value"]) . "'";
				break;
				case "object"	:
				case "resource"	:
				case "array" 	:
					self::$js_variables[$key]["value"] = "'" . str_replace("'", "\'", json_encode($value["value"])) . "'";
				break;
				case "NULL" 	:
				default 		:
					self::$js_variables[$key]["value"] = 'null';
				break;
			}
		}

		self::assign('_JSVariables', self::$js_variables);

		// Affichage du HTML & fin d'execution

		self::display('../framework/layout.html');

		mysql::close();

		exit($status);
	}

	/**
	* Indique que la page est introuvable, envoi une 404 et quitte
	* @param  $message   Le message a afficher (la raison)
	*
	* @access protected
	*/
	protected static function not_found($message = '') {
		header("HTTP/1.0 404 Not Found");
		self::$robots = "noindex, nofollow";
		self::assign('Error404Reason', $message);
		self::log('404');
		require_once(__DIR__ . "/../controllers/ErrorDocument404.php");
		self::quit();
	}

	/**
	* Assign une variable a smarty, elle sera ensuite utilisee dans le template html
	* @param $key   Le nom de la variable pour le fichier html
	* @param $value Le contenu de cette variable
	*
	* @access protected
	*/
	protected static function assign($key, $value) { self::$smarty->assign($key, $value); }

	/**
	* Affiche le fichier html passe en parametre
	* @param $html Le path vers le fichier html
	*
	* @access private
	*/
	private static function display($html) { self::$smarty->display($html); }

	/**
	* Redirige vers une autre page
	* @param $url L'url de la page
	*
	* @access protected
	*/
	protected function redirection($url, $code = 302) {
		mysql::close();
		header("Location: " . $url, true, $code);
		exit(0);
	}

	/**
	* Defini si nous sommes en production ou en developpement (par defaut = production)
	* @param $environment PROD || DEV
	*
	* @access protected
	*/
	protected function set_environment($environment) {
		self::$environment = $environment;
		if ($environment == DEV) {
			self::$robots = "noindex, nofollow";
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		}
		else {
			ini_set('display_errors', 0);
			error_reporting(0);
		}
	}

	/**
	* Creer un fichier cache rassemblant les documents d'un array
	* @param $array La liste des fichiers
	* @param $type Dans quel repertoire est contenu ce fichier
	*
	* @access private
	*/
	private function cache_generator(&$array, $type = '', $force = false) {

		if (!empty($array) && ($type == 'js' || $type == 'css')) { $directory = __DIR__ . '/../' . $type . '/'; }
		else return (array());

		$total_mtime = 0;
		$total_string = '';
		$ext_array = array();
		foreach ($array as $value) {
			if (filter_var($value, FILTER_VALIDATE_URL) === false) {
				if (file_exists($directory . $value)) {
					$total_mtime += filemtime($directory . $value);
					$total_string .= substr($value, -5);
				}
				else return (array());
			}
			else $ext_array []= $value;
		}

		if ($total_mtime == 0) return (array());

		$total_mtime = md5($total_string . $total_mtime);

		if (!file_exists(__DIR__ . '/../cache/' . $total_mtime . '.' . $type) || $force == true) {
			if (!file_exists(__DIR__ . '/../cache/') || !is_dir(__DIR__ . '/../cache/'))
				mkdir(__DIR__ . '/../cache/');

			$filecontent = '';
			foreach ($array as $value) {
				if (filter_var($value, FILTER_VALIDATE_URL) === false)
					$filecontent .= file_get_contents($directory . $value, FILE_USE_INCLUDE_PATH);
			}

			if (empty($filecontent)) return (array());

			if ($type == 'js') $ch = curl_init('http://javascript-minifier.com/raw');
			else $ch = curl_init('http://cssminifier.com/raw');

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('input' => $filecontent));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$minified = curl_exec($ch);

			curl_close($ch);

			if (empty($minified)) $minified = $filecontent;
			if (!file_put_contents(__DIR__ . '/../cache/' . $total_mtime . '.' . $type, $minified)) return (array());
		}
		$array = array($_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["HTTP_HOST"] . '/cache/' . $total_mtime . '.' . $type);
		foreach ($ext_array as $value) { $array []= $value; }
	}

	/**
	* Retourne la traduction d'une ligne de texte
	* @param $params Les parametres du tag smarty (unused)
	* @param $name Le nom de la traduction
	* @param $smarty
	*
	* @access public
	*/
	public static function translate($params = null, $name, $smarty) {

		$translation = '';
		if (!is_null($name) && array_key_exists($name, self::$languages)) {

			$translation = self::$languages[$name];
			preg_match_all('/##([^#]+)##/i', $translation, $vars, PREG_SET_ORDER);

			foreach($vars as $var) { $translation = str_replace($var[0], $smarty->getTemplateVars($var[1]), $translation); }
		}
		return ($translation);
	}

	public static function controller_exists($controller) {
		if (file_exists(__DIR__ . "/../controllers/" . $controller . ".php"))
			return (true);
		return (false);
	}

	public static function change_controller($controller) {
		if (self::controller_exists($controller)) {
			require_once(__DIR__ . "/../controllers/" . $controller . ".php");
			self::quit();
		}
		else self::not_found();
	}

	public static function log($categorie, $message = '') {

		if (!empty($message)) $message = ' - ' . $message;

		$logmessage = date('Y-m-d H:i:s') . ' - [' . $categorie . '] - ' . $_SERVER['REMOTE_ADDR'] . ' - ' . $_SERVER["REQUEST_METHOD"] . ' - ' . _::$siteurl . $_SERVER["REQUEST_URI"] . $message . "\n";
		file_put_contents(__DIR__ . '/../logs/' . date('Y-m-d') . '.log', $logmessage, FILE_APPEND);
	}
}

?>