<?php

define('INCLUDE_CHECK',true);
require './config/connect.php';
require './config/session.inc.php';
//require_once 'header.php'; 

if($_SESSION['id'] && $_SESSION['admin'] == '1' ) :

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Търсене на обект</title>

  <link type="text/css" href="./css/main-new.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="./css/main.css" media="screen" />



    <script>
	function selectObject( patrul )
	{

        if ( patrul != "") {
            var patrul	= patrul;
        } else {
            var patrul	= 0;
        }

        if( document.getElementById('oNum') ) {
            var oNum	= document.getElementById('oNum'	).value;
        } else {
            var oNum = 0;

        }
	
		   
        if (window.XMLHttpRequest)
          {// code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp=new XMLHttpRequest();
          }
        else
          {// code for IE6, IE5
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
          }
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
            document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
            }
          }
        xmlhttp.open("GET","./ajax_scripts/get_object.php?oNum="+oNum+"&patrul="+patrul,true);
        xmlhttp.send();
	}

	function onFormKeyNum( event )
	{	
	    var keyCode = document.all? event.keyCode : event.which;
	    
	    var dRes = ( keyCode >= 0x30 && keyCode <= 0x39 );
	    
	    event.returnValue = dRes;
	    
	    return dRes;
	}
		
	</script>
    
</head>

<body>

<form>

<input type="text" name="oNum" id="oNum" class="login" onkeypress="onFormKeyNum(event);" style="float: left;" />
<label class="login" style="text-align: left;"> <a onclick="selectObject();" style="font-size: 14px;" > &nbsp; &nbsp; Търси </a></label>
<div class="clear"></div>
</form>
<div id="txtHint">Въведете номер на обект за който желаете да предизвикате аларма!</div>

</body>
</html>
<?php 
endif;
?>