<?
//СЛОВОМ
	function slovom ($sum_lv, $_currency, $_currency_100) {
		$sum_lv = str_replace(" ", "", $sum_lv);  $sum_lv = number_format($sum_lv, 2, '.', '');
	    $edinici=array("","един","два","три","четири","пет","шест","седем","осем","девет");
	    $ot10do19=array("десет","единадесет","дванадесет","тринадесет","четиринадесет","петнадесет", "шестнадесет","седемнадесет","осемнадесет","деветнадесет");
	    $desettici=array("","десет","двадесет","тридесет","четиридесет","петдесет","шестдесет", "седемдесет","осемдесет","деветдесет");
	    $stotici=array("","сто","двеста","триста","четиристотин","петстотин","шестстотин","седемстотин","осемстотин","деветстотин");
	    $hiliadi=array("хиледи","една хиледи","две хиляди","три хиляди","четири хиляди","пет хиляди", "шест хиляди", "седем хиляди","осем хиляди","девет хиляди");
	    $ot10do19hiliadi=array("десет хиляди","единадесет хиляди","дванадесет хиляди","тринадесет хиляди","четиринадесет хиляди","петнадесет хиляди","шестнадесет хиляди","седемнадесет хиляди","осемнадесет хиляди","деветнадесет хиляди");
	    $stotinki=intval(substr($sum_lv,strlen($sum_lv)-2,2));
	    $levove_str=substr($sum_lv,0,strlen($sum_lv)-3);
	    $levove_len=strlen($levove_str);
	    $levove=intval($levove_str);
	    $levove_slovom="";
	    $not_i=true;
	    $empty_levove_slovom=true;
	    
	    if ($levove_len > 0 ) {
		$levove_slovom=$edinici[intval(substr($levove_str,$levove_len-1,1))]." $levove_slovom";
		if(intval(substr($levove_str,$levove_len-1,1))!=0){$empty_levove_slovom=false;}
	    }
	    if ($levove_len > 1 ) {
		if(intval(substr($levove_str,$levove_len-2,1))==1) {
		    $levove_slovom="";
		    $levove_slovom=$ot10do19[intval(substr($levove_str,$levove_len-1,1))]." $levove_slovom";
		    $empty_levove_slovom=false;
		}
		else
		{
		    if(intval(substr($levove_str,$levove_len-2,1))!=0 && !$empty_levove_slovom && $not_i){
			$levove_slovom=" и ".$levove_slovom; $not_i=false;
		    }
		    $levove_slovom=$desettici[intval(substr($levove_str,$levove_len-2,1))]." $levove_slovom";
		    if(intval(substr($levove_str,$levove_len-2,1))!=0){$empty_levove_slovom=false;}
		}
	    }
	    
	    if ($levove_len > 2 ){
		if(intval(substr($levove_str,$levove_len-3,1))!=0 && !$empty_levove_slovom && $not_i){
		    $levove_slovom=" и ".$levove_slovom; $not_i=false;
		}
		$levove_slovom=$stotici[intval(substr($levove_str,$levove_len-3,1))]." $levove_slovom";
		if(intval(substr($levove_str,$levove_len-3,1))!=0){
		    $empty_levove_slovom=false;
		}
	    }
	
	    $hiliadi_slovom=''; $not_i=false;
	    if ($levove_len > 3 ) {
		$hiliadi_slovom=$hiliadi[intval(substr($levove_str,$levove_len-4,1))]." $hiliadi_slovom";
		if(intval(substr($levove_str,$levove_len-4,1))!=0){$empty_levove_slovom=false;$not_i=true;}
	    }
	    
	    if ($levove_len > 4 ){
		if(intval(substr($levove_str,$levove_len-5,1))==1) {
		    $hiliadi_slovom="";
		    $hiliadi_slovom=$ot10do19hiliadi[intval(substr($levove_str,$levove_len-4,1))]." $hiliadi_slovom";
		    $empty_levove_slovom=false;
		}
		else {
		  if(intval(substr($levove_str,$levove_len-5,1))!=0 && !$empty_levove_slovom && $not_i)
		    {$hiliadi_slovom=" и ".$hiliadi_slovom; $not_i=false; }
		  $hiliadi_slovom=$desettici[intval(substr($levove_str,$levove_len-5,1))]." $hiliadi_slovom";
		  if(intval(substr($levove_str,$levove_len-5,1))!=0){$empty_levove_slovom=false;}
		}
	    }
		  if ($levove_len > 5 ) {
		    if(intval(substr($levove_str,$levove_len-6,1))!=0 && !$empty_levove_slovom && $not_i)
		      {$hiliadi_slovom=" и ".$hiliadi_slovom; $not_i=false;}
		  $hiliadi_slovom=$stotici[intval(substr($levove_str,$levove_len-6,1))]." $hiliadi_slovom";
		  if(intval(substr($levove_str,$levove_len-6,1))!=0){$empty_levove_slovom=false;}
	    }
	
		if($empty_levove_slovom){$levove_slovom="нула";}
		if($hiliadi_slovom!='') $levove_slovom= "$hiliadi_slovom и $levove_slovom";
		$levove_slovom = preg_replace('/\s\s+/', ' ', $levove_slovom); $levove_slovom = rtrim($levove_slovom);
		$stotinki = preg_replace('/\s\s+/', ' ', $stotinki); $stotinki = rtrim($stotinki);
		return	"$levove_slovom $_currency и $stotinki $_currency_100";
	};

?>