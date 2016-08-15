<?php

if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));

function random($tab) { return ($tab[array_rand($tab)]); }

function getCode($length) {
	$chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
	$code = '';

	for ($i = 0 ; $i < $length ; $i++) { $code .= $chars { mt_rand(0, strlen($chars) - 1) }; }
	return ($code);
}

session_start();

define('CODE_LENGTH', 5);

$code = getCode(CODE_LENGTH);
$chars = [];

$_SESSION['captcha'] = md5($code);

for ($i = 0 ; $i < CODE_LENGTH ; $i++) { $chars []= substr($code, $i, 1); }

$fonts = glob(__DIR__ . '/../fonts/captcha/*.ttf');

$image = imagecreatefrompng(__DIR__ . '/../images/captcha.png');

$colors = array (imagecolorallocate($image, 10, 10, 10),
				 imagecolorallocate($image, 30, 30, 30),
				 imagecolorallocate($image, 50, 50, 50),
				 imagecolorallocate($image, 70, 70, 70),
				 imagecolorallocate($image, 90, 90, 90));

for ($i = 0 , $spacing = 10 ; $i < CODE_LENGTH ; $i++ , $spacing += 30) {
	imagettftext($image, 26, rand(-20, 20), $spacing, rand(35, 45), random($colors), random($fonts), $chars[$i]);
}

header('Cache-Control: max-age=0');
header('Content-Type: image/png');

imagepng($image);

imagedestroy($image);

?>