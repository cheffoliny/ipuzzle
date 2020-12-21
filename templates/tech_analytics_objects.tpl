{literal}
<script type="text/javascript">
    
	rpc_debug = true;
 	rpc_excel_panel = "off";
 	

 	function loadObject( id )
 	{	
 		
		var	nIDObject = id;
		var	nIDOffice = $('nIDOffices').value;
		
		window.opener.PopUpHandler( nIDObject, nIDOffice );
	
 	}
 	
</script>
{/literal}

<form id="form1" name="form1" onsubmit="return( false );">

<table class = "page_data">
	<tr>
		<td class="page_name">Обекти</td>
	</tr>
</table>
	
<table class="search" style="margin-left = 30px;">
	
	 <tr align="left">
		<td> Фирма:
			&nbsp;
			<select id="nIDFirms" name="nIDFirms" onchange=" loadXMLDoc2( 'loadOffices' ); " />
		</td>
		<td> Регион:
			&nbsp;
			<select type="text" id="nIDOffices" name="nIDOffices" onchange=" loadXMLDoc2( 'result' );" />
		</td>
		<td> Тип:
			&nbsp;
			<select id="nConfirmed" name="nConfirmed" style="width: 110px;" >
			<option value="0">Непотвърдени</option>
			<option value="1">Потвърдени</option>
			</select>
		</td>
	</tr>
	<tr align="left">
		<td> Име:
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="text" id="sObjectName" name="sObjectName" style="width: 200px;" />
		</td>
		<td> Статус:
			&nbsp;&nbsp;
			<select type="text" id="nStatus" name="nStatus" onchange = " loadXMLDoc2( 'loadStatuses' );" />
		</td>
		<td align="right">
			<button id="b70" name="Button" onClick="return loadXMLDoc2( 'result' );" ><img src="images/confirm.gif"> Търси </button>
		</td>
	</tr>
	
</table>

<hr/>

<div id="result"> </div>

</form>


 {literal}
<script type="text/javascript">
	
	
	loadXMLDoc2( 'loadFirms' );
	
	
rpc_on_exit = function ()
	{ 
		rpc_on_exit = function () {
			rpc_on_exit = function () {}
			loadXMLDoc2( 'loadStatuses');
		}
		loadXMLDoc2( 'loadOffices' );
	}	

</script>
{/literal}