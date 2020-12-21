<?php
// ПРИМЕР:
//barcode.php?code=code128&pcode=abc12345&text=0&height=10&resolution=1&$img_type=jpg&$font_size=10
//http://other.lookstrike.com/barcode/download.php

$code			= !empty ($_GET['code']) 		? $_GET['code'] 		: 'code128';
$pcode			= !empty ($_GET['pcode']) 		? $_GET['pcode'] 		: '12348';
$text			= !empty ($_GET['text']) 		? $_GET['text'] 		: 1;
$height 		= !empty ($_GET['height']) 		? $_GET['height'] 		: 60;
$resolution 	= !empty ($_GET['resolution']) 	? $_GET['resolution'] 	: 2;

$checksum		= !empty ($_GET['checksum']) 	? $_GET['checksum'] 	: false;
$start			= !empty ($_GET['start']) 		? $_GET['start'] 		: 'B';
$book			= !empty ($_GET['book']) 		? $_GET['book'] 		: false;
$font_name 		= !empty ($_GET['font_name']) 	? $_GET['font_name'] 	: 'Arial.ttf';
$font_size 		= !empty ($_GET['font_size']) 	? $_GET['font_size'] 	: 18;


$img_type		= !empty ($_GET['img_type']) 	? $_GET['img_type'] 	: 'png';
					$img_type = ($img_type=='jpg') || ($img_type=='png') ? $img_type : 'jpg'; 


if (!empty($_GET)){
	extract($_GET, EXTR_OVERWRITE);
}else if (!empty($HTTP_GET_VARS)){
	extract($HTTP_GET_VARS, EXTR_OVERWRITE);
}

$PHP_SELF= $_SERVER['PHP_SELF'];

// Define variable to prevent hacking
define('IN_CB',true);

require('class/index.php');
require('class/Font.php');
require('class/FColor.php');
require('class/BarCode.php');
require('class/FDrawing.php');


// Зареждане на шрифта
$font = null;

if ($text)
	$font =& new Font(sprintf('./class/font/%s', $font_name), $font_size);

// Дефиниране на цвят от  (аргументи R, G, B)
$color_black =& new FColor(0,0,0);  
$color_white =& new FColor(255,255,255);

/* Параметри:
1 - Височина на линиите в пиксели
2 - Цвят на линиите
3 - Цвят на разстоянието м/у линиите
4 - Резолюция
5 - Текст
6 - Шрифт на текста */



switch ($code) {
	case 'code11':
		include('class/code11.barcode.php');
		$oCode =& new code11($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	case 'code39':
		include('class/code39.barcode.php');
		$oCode =& new code39($height, $color_black, $color_white, $resolution, $pcode, $font, $checksum);
		break;
	case 'code93':
		include('class/code93.barcode.php');
		$oCode =& new code93($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;	
	case 'ean13':
		include('class/ean13.barcode.php');
		$oCode =& new ean13($height, $color_black, $color_white, $resolution, $pcode, $font, $book);
		break;
	case 'ean8':
		include('class/ean8.barcode.php');
		$oCode =& new ean8($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	case 'i25':
		include('class/i25.barcode.php');
		$oCode =& new i25($height, $color_black, $color_white, $resolution, $pcode, $font, $checksum);
		break;
	case 'msi':
		include('class/msi.barcode.php');
		$oCode =& new msi($height, $color_black, $color_white, $resolution, $pcode, $font, $checksum);
		break;
	case 'postnet':
		include('class/postnet.barcode.php');
		$oCode =& new postnet($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	case 's25':
		include('class/s25.barcode.php');
		$oCode =& new s25($height, $color_black, $color_white, $resolution, $pcode, $font, $checksum);
		break;
	case 'upca':
		include('class/upca.barcode.php');
		$oCode =& new upca($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	case 'upce':
		include('class/upce.barcode.php');
		$oCode =& new upce($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	case 'code11':
		include('class/code11.barcode.php');
		$oCode =& new code11($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	case 'othercode':
		include('class/othercode.barcode.php');
		$oCode =& new othercode($height, $color_black, $color_white, $resolution, $pcode, $font);
		break;
	default:
		include('class/code128.barcode.php');
		$oCode =& new code128($height, $color_black, $color_white, $resolution, $pcode, $font, $start);
}




//$oCode =& new code39($height, $color_black, $color_white, $resolution, $pcode, $font);

/* 
1 - Име на файл (default : директно ще се визуализира на екрана)
2 - Цвят на фона */
$drawing =& new FDrawing('',$color_white);
$drawing->setBarcode($oCode);
$drawing->draw();

// Header that says it is an image (remove it if you save the barcode to a file)
$header = sprintf ('Content-Type: image/%s', $img_type); 
header($header);

$drawing->finish($img_type);
?>