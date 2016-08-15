<?php

function encode_for_url($url) {
	$url = str_replace('/', '-', $url);
	$url = str_replace('"', '-', $url);
	$url = str_replace('"', '-', $url);
	$url = str_replace('%', '-', $url);
	$url = str_replace('+', '-', $url);

	$url = trim($url);

	$url = htmlentities($url, ENT_NOQUOTES, 'UTF-8');

	$patterns = array(
		'#&([A-Za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#',
		'#&([A-Za-z]{2})(?:lig);#',
		'#&[^;]+;#',
		'#([^a-z0-9/]+)#i',
		);

	$remplacements = array(
		'\1',
		'\1',
		'',
		'-',
		);

	$url = preg_replace($patterns, $remplacements, $url);

	$url = strtolower($url);

	return ($url);
}

function generate_random_string($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return ($randomString);
}

function clean_array($tab) {
	$ret = array();

	foreach ($tab as $key => $value)
		if (!empty($value)) {
			if (is_numeric($key))
				$ret []= $value;
			else $ret[$key] = $value;
		}

	return ($ret);
}

function array_sort($array, $key, $direction = SORT_ASC) {
	$tmp = array();

	foreach ($array as $k => $v)
		$tmp []= $v[$key];

	array_multisort($tmp, $direction, $array);
	return ($array);
}

function array_insert(&$array, $position, $insert) {
	if (is_int($position)) {
		array_splice($array, $position, 0, $insert);
	} else {
		$pos   = array_search($position, array_keys($array));
		$array = array_merge(
			array_slice($array, 0, $pos),
			$insert,
			array_slice($array, $pos)
			);
	}
}

function swap(&$a, &$b) {
	$a = $a ^ $b;
	$b = $a ^ $b;
	$a = $a ^ $b;
}

function substrws($text, $len) {
	if ((strlen($text) > $len)) {
		$whitespaceposition = strpos($text, " ", $len) - 1;

		if ($whitespaceposition > 0)
			$text = substr($text, 0, ($whitespaceposition + 1));

		if (preg_match_all("|<([a-zA-Z]+)>|", $text, $aBuffer)) {
			if (!empty($aBuffer[1])) {
				preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);
				if (count($aBuffer[1]) != count($aBuffer2[1])) {
					foreach ($aBuffer[1] as $index => $tag) {
						if (empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
							$text .= '</' . $tag . '>';
					}
				}
			}
		}
	}
	return ($text);
}

?>