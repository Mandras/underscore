<?php

if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));

if (!isset($_SERVER["REQUEST_URI"])) $_SERVER["REQUEST_URI"] = 'images/not_found.png';

$imageurl = current(explode('?', $_SERVER["REQUEST_URI"]));

if ($imageurl[0] == '/') $imageurl = substr($imageurl, 1);

if (!file_exists(__DIR__ . '/' . $imageurl)) {
	if (!file_exists(__DIR__ . '/images/not_found.png')) {
		header("HTTP/1.0 404 Not Found");
		exit(1);
	}
	$imageurl = 'images/not_found.png';
}

$exif = exif_imagetype(__DIR__ . '/' . $imageurl);

switch ($exif) {
	case 'IMAGETYPE_JPEG':	$content_type = 'image/jpeg';	break;
	case 'IMAGETYPE_WBMP':	$content_type = 'image/wbmp';	break;
	case 'IMAGETYPE_PNG':	$content_type = 'image/png';	break;
	case 'IMAGETYPE_GIF':	$content_type = 'image/gif';	break;
	case 'IMAGETYPE_ICO':	$content_type = 'image/ico';	break;
	case 'IMAGETYPE_BMP':	$content_type = 'image/bmp';	break;
	case 'IMAGETYPE_SWF':	$content_type = 'image/swf';	break;
	case 'IMAGETYPE_PSD':	$content_type = 'image/psd';	break;
	case 'IMAGETYPE_JPC':	$content_type = 'image/jpc';	break;
	case 'IMAGETYPE_JP2':	$content_type = 'image/jp2';	break;
	case 'IMAGETYPE_JPX':	$content_type = 'image/jpx';	break;
	case 'IMAGETYPE_JB2':	$content_type = 'image/jb2';	break;
	case 'IMAGETYPE_SWC':	$content_type = 'image/swc';	break;
	case 'IMAGETYPE_IFF':	$content_type = 'image/iff';	break;
	case 'IMAGETYPE_XBM':	$content_type = 'image/xbm';	break;
	default:				$content_type = 'image';		break;
}

header("HTTP/1.0 200 OK");
header('Cache-Control: max-age=86400');
header('Content-type:' . $content_type);
header('Content-Length: '. filesize(__DIR__ . '/' . $imageurl));

echo(readfile(__DIR__ . '/' . $imageurl));
exit(0);

?>