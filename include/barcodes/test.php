<?php
// Define variable to prevent hacking
define('IN_CB',true);

// Including all required classes
require('class/index.php');
require('class/Font.php');
require('class/FColor.php');
require('class/BarCode.php');
require('class/FDrawing.php');

// Including the barcode technology
include('class/code39.barcode.php');

// Loading Font
$font =& new Font('./class/font/Arial.ttf', 18);

// Creating some Color (arguments are R, G, B)
$color_black =& new FColor(0,0,0);
$color_white =& new FColor(255,255,255);

/* Here is the list of the arguments:
1 - Thickness
2 - Color of bars
3 - Color of spaces
4 - Resolution
5 - Text
6 - Text Font */
$code =& new code39(60,$color_black,$color_white,2,'HELLO',$font);

/* Here is the list of the arguments
1 - Filename (empty : display on screen)
2 - Background color */
$drawing =& new FDrawing('',$color_white);
$drawing->setBarcode($code);
$drawing->draw();

// Header that says it is an image (remove it if you save the barcode to a file)
header('Content-Type: image/png');

// Draw (or save) the image into PNG format.
$drawing->finish(IMG_FORMAT_PNG);
?>