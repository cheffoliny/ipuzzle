<?php
	session_start();
	
	$nRandomNum = mt_rand(0, 1000000);
	$sSiteKey = "SsdfkQSd5^552kslF^@sj%W@Y62-dm29-.-39.3a8sUf+W9";
	$sDateKey = date("F j");
    $nRCode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . $sSiteKey . $nRandomNum . $sDateKey));
    $nCode = substr($nRCode, 2, 4);
    $hImage = ImageCreateFromJPEG("../images/code_bg.jpg");
    $nTextColor = ImageColorAllocate($hImage, 80, 80, 80);
    ImageString ($hImage, 5, 23, 2, $nCode, $nTextColor);
    ImageJPEG($hImage, '', 75);
	$_SESSION['login_img_num'] = $nCode;
?>