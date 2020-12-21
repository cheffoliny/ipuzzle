<?php
if(isset($_GET['code']) && isset($_GET['t']) && isset($_GET['r']) && isset($_GET['text']) && isset($_GET['f1']) && isset($_GET['f2']) && isset($_GET['o']) && isset($_GET['a1']) && isset($_GET['a2'])){
	define('IN_CB',true);
	require('config.php');
	require($class_dir.'/index.php');
	require($class_dir.'/FColor.php');
	require($class_dir.'/BarCode.php');
	require($class_dir.'/FDrawing.php');
	require($class_dir.'/Font.php');
	if(include($class_dir.'/'.$_GET['code'].'.barcode.php')){
		if($_GET['f1'] !== '0' && intval($_GET['f2']) >= 1){
			$font = new Font($class_dir.'/font/'.$_GET['f1'], intval($_GET['f2']));
		} else {
			$font = 0;
		}
		$color_black = new FColor(0,0,0);
		$color_white = new FColor(255,255,255);
		if(!empty($_GET['a2']))
			$code_generated = new $_GET['code']($_GET['t'],$color_black,$color_white,$_GET['r'],$_GET['text'],$font,$_GET['a1'],$_GET['a2']);
		elseif(!empty($_GET['a1']))
			$code_generated = new $_GET['code']($_GET['t'],$color_black,$color_white,$_GET['r'],$_GET['text'],$font,$_GET['a1']);
		else
			$code_generated = new $_GET['code']($_GET['t'],$color_black,$color_white,$_GET['r'],$_GET['text'],$font);
		$drawing = new FDrawing('',$color_white);
		$drawing->add_barcode($code_generated);
		$drawing->draw_all();
		$drawing->finish(intval($_GET['o']));
	}
	else{
		header('Content: image/png');
		readfile('error.png');
	}
}
else{
	header('Content: image/png');
	readfile('error.png');
}
?>