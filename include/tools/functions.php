<?

function array_multi_csort() {
	$args = func_get_args();
	$marray = array_shift($args);
	$i = 0;

  $msortline = "return(array_multisort(";
	foreach ($args as $arg) {
		$i++;
		if (is_string($arg)) {
			foreach ($marray as $row) {
				$sortarr[$i][] = $row[$arg];
			}
		}
		else {
			$sortarr[$i] = $arg;
		}
		$msortline .= "\$sortarr[".$i."],";
	}
	
	$msortline .= "\$marray));";
	eval($msortline);
	return $marray;
}

function compact_to_string ($arr_data) {
	if (is_array($arr_data)) {
		$str = "(";
		if (count($arr_data))	{
			foreach($arr_data as $value) $str .= "$value,";
			$str = substr($str,0,-1);
		}
		$str .= ")";
	}
	else $str = "($arr_data)";
	return $str;
}

function compact_to_string_str ($arr_data) {
	if (is_array($arr_data)) {
		$str = "(";
		if (count($arr_data))	{
			foreach($arr_data as $value) $str .= "'$value',";
			$str = substr($str,0,-1);
		}
		$str .= ")";
	}
	else $str = "('$arr_data')";
	return $str;
}

#	Funkciq koqto preglezhda nali4nite izobrazheniq s avtomobili i nali4nite takiva sys sqnka
#	Ako go nqma izobrazhenieto go syzdava.
#
#
function CheckAndFillImg($dir,$color)
{
	$ds = new dropShadow(FALSE);
	$ds->setShadowPath('include/shadow/');
	$path = "images/auto/shadow_".$dir."/";
	$dir = dir("images/auto");
	while($file = $dir->read())
	{
		if (preg_match('/(.*).(?:gif|png|jpg)/',$file,$regs))
		{
			if(!file_exists($path.$regs[1].".png"))
			{
				$ds->loadImage("images/auto/".$file);
				$ds->resizeToSize(100,60);
				$ds->applyShadow($color);
				$ds->saveShadow($path.$file,'png');
			}
			
		}
	}
}
# end of function

function GetAdjacentTable($tbl, $offset)
{
	return date("Y_m",mktime(0,0,0,substr($tbl,5,2) + $offset,1,substr($tbl,0,4)));
}

function getMTime()
{
	list($usec, $sec) = explode(" ",microtime());	return ((float)$usec + (float)$sec);
}	

function gd_put_text($fname,$text,$id)
{
	$fd = fopen($fname,"r");
	$image = fread($fd,filesize($fname));
	fclose($fd);
	
	#$font = "/usr/home/ntoshkov/www/auto/chicago.ttf";
	$font = 'chicago.ttf';
	
	$nim = @ImageCreateTrueColor(100,60);
	imagealphablending($nim,TRUE);
	imageantialias($nim,TRUE);
	
	$im = @ImageCreateFromString($image);
	imagecopymerge($nim,$im,0,0,0,0,100,60,100);
	
	$img_data = @ImageTTFBBox(30,0,$font,$text);
	$sx = $img_data[4] + $img_data[0];
	$sy = $img_data[1] - $img_data[5];
	$x = (100 - $sx)/2;
	$y = 60 - (60 - $sy)/2;
	
	$c1 = @imagecolorallocatealpha($nim, 255, 255, 255, 0);
	$c2 = @imagecolorallocatealpha($nim, 127, 127, 127, 32);
	$c3 = @imagecolorallocatealpha($nim, 0, 0, 0, 64);
	ImageTTFText($nim,30,0,$x+2,$y-2,$c3,$font,$text);
	ImageTTFText($nim,30,0,$x+1,$y-1,$c2,$font,$text);
	ImageTTFText($nim,30,0,$x,$y,$c1,$font,$text);
	$nfname = "images/auto/sod/". $id . "_" . $text . ".png";
	ImagePNG($nim,$nfname);
	return $fname;
}

# Записвва изображение под име $ofname . jpg в път $path,ресемплирано до $size размер - квадрат и центрирано в него
# v1 - фиксирани цветове за фон и рамка
function gd_thumbnail($ifname, $ofname, $path, $size = 72, $distance = 3, $quality = 80)
{
	$fp = fopen($ifname,"r");
	$fdata = fread($fp,filesize($ifname));
	fclose($fp);
	
	$im = ImageCreateFromString($fdata);

	$bsize = $size - $distance;
	$sx = ImageSX($im);
	$sy = ImageSY($im);
	
	if ($sx >= $sy)
	{
		$ratio = $bsize / $sx;
		$scale_X = $bsize;
		$scale_Y = floor($sy * $ratio);
	}
	else
	{
		$ratio = $bsize / $sy;
		$scale_Y = $bsize;
		$scale_X = floor($sx * $ratio);
	}

	$nim = ImageCreateTrueColor($size,$size);
	$white = ImageColorAllocate($nim,0xEC,0xF4,0xE3);
	$border = ImageColorAllocateAlpha($nim,0x72,0x9D,0x49,0x00);
	$sh1 = ImageColorAllocateAlpha($nim,0x30,0x30,0x30,0x70);
		
	$top = floor(($size - $scale_Y) / 2);
	$left = floor(($size - $scale_X) / 2);
	
	ImageFill($nim,0,0,$white);
	ImageRectangle($nim,0,0,$size-1,$size-1,$border);
	ImageFilledRectangle($nim,$left+1,$top+1,$left+$scale_X,$top+$scale_Y,$sh1);
	ImageFilledRectangle($nim,$left+2,$top+2,$left+$scale_X+1,$top+$scale_Y+1,$sh1);
	ImageCopyResampled($nim,$im,$left,$top,0,0,$scale_X,$scale_Y,$sx,$sy);
	ImageRectangle($nim,$left,$top,$left+$scale_X-1,$top+$scale_Y-1,$border);
	
	$result = ImageJPEG($nim,$path.$ofname.".jpg",$quality);
	ImageDestroy($im);
	ImageDestroy($nim);
	
	return $result;
}

#
function ImageCopyResampleBicubic (&$dimg, &$simg, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh)
{
  ImagePaletteCopy ($dimg, $simg);
  $rX = $sw / $dw;
  $rY = $sh / $dh;
  $nY = 0;
  for ($y=$dy; $y<$dh; $y++)
	{
		$oY = $nY;
	  $nY = intval(($y + 1) * $rY+.5);
	  $nX = 0;
	  for ($x=$dx; $x<$dw; $x++)
		{
			$r = $g = $b = $a = 0;
			$oX = $nX;
			$nX = intval(($x + 1) * $rX+.5);
			for ($i=$nY; --$i>=$oY;)
			{
				for ($j=$nX; --$j>=$oX;)
				{
					$c = ImageColorsForIndex ($simg, ImageColorAt ($simg, $j, $i));
					$r += $c['red'];
					$g += $c['green'];
					$b += $c['blue'];
					$a++;
				}
			}
			ImageSetPixel ($dimg, $x, $y, ImageColorClosest ($dimg, $r/$a, $g/$a, $b/$a)); 
		}
	}
}

#
function gd_thumbnail_mem(&$img, $file, $x, $y, $ext = 'png')
{
	if (!function_exists("Image$ext")) $ext = 'png';
	if ($im = @ImageCreateTrueColor($x,$y))
	{
		if (function_exists('ImageCopyResampled'))
			ImageCopyResampled($im, $img, 0, 0, 0, 0, $x, $y, ImageSX($img), ImageSY($img));
		else
			ImageCopyResampleBicubic($im, $img, 0, 0, 0, 0, $x, $y, ImageSX($img), ImageSY($img));
		$func = "Image$ext";
		@$func($im,$file);
	}
}


# neobhodimo exif_lib
function is_image($string)
{
	if (function_exists('exif_imagetype'))
		$it = exif_imagetype($string);
	else
	{
		$attr = getimagesize($string);
		$it = $attr[2];
	}
	return ($it == IMAGETYPE_GIF || $it == IMAGETYPE_JPEG || $it == IMAGETYPE_PNG);
}

#
#
function CheckAndFill(&$db,$path)
{
	$drivers_tn_preffix = 'drv_';
	$ftype = 'png';

	$res_ids = $db->Execute("SELECT id_person, dat FROM access_reg WHERE person_type = 1 AND vacate = '0' ORDER BY id_person;");
	
	$drivers = array();
	while(!$res_ids->EOF)
	{
		$value = $res_ids->fields;
		$cfile = "{$path}/{$drivers_tn_preffix}{$value['id_person']}.{$ftype}";
		if (!file_exists($cfile) || date("YmdHis",filemtime($cfile)) < $value['dat']) array_push($drivers,$value['id_person']);
		$res_ids->MoveNext();
	}
	$array_diff_str = compact_to_string($drivers);
	# !Note da se izpolzva $array_del za iztrivane na nenuzhni zapisi..
	
	$counter = count($drivers);
	if ($counter)
	{
		$start = 0;
		$step = 10;
	
		while($start < $counter)
		{
			$img_data = $db->Execute("SELECT id_person, photo FROM access_reg WHERE id_person IN $array_diff_str LIMIT $start, $step");
			if ($img_data)
			while(!$img_data->EOF)
			{
				$value = $img_data->fields;
				if ($value['photo'])
				{
					$im = ImageCreateFromString($value['photo']);
					if ((@ImageSX($im) < 15)||(@ImageSY($im) < 20))
					{
						ImageDestroy($im);
						$im = ImageCreateFromJPEG("images/noface.jpg");
					}
				}
				else
				{
					$im = ImageCreateFromJPEG("images/noface.jpg");
				}
				gd_thumbnail_mem($im, "{$path}/{$drivers_tn_preffix}{$value['id_person']}.{$ftype}", 15, 20, $ftype);
				$img_data->MoveNext();
			}
			$start += $step;
		}
	}
}


# vryshta GET string
function arr_to_arg($args)
{
	$str = '?';
	foreach($args as $key => $value) $str .= "$key=$value&";
	return $str;
}

# time function
function getCounter()
{
	list($usec, $sec) = explode(" ",microtime());	return ((float)$usec + (float)$sec);
}

# return array of sums by elements
function array_elements_sum($arr1,$arr2)
{
	foreach($arr1 as $key => $value) $arr[$key] = isset($arr2[$key]) ? $value + $arr2[$key] : $value;
	return $arr;
}

# return date difference in days. Requires timestamp date
function date_diff($date1, $date2)
{
	return ceil((max($date1,$date2)-min($date1,$date2))/(24*3600));
}
?>