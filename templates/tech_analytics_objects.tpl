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

<form id="form1" name="form1" class="ui-nomenclature-list ui-technical-list ui-tech-analytics-objects" onsubmit="return( false );">

<table class="page_data ui-nomenclature-heading ui-technical-heading">
	<tr>
		<td class="page_name">Обекти</td>
	</tr>
</table>
	
<table class="search ui-nomenclature-filter ui-technical-filter">
	
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
			<button type="button" id="b70" name="Button" class="search" onClick="return loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси </button>
		</td>
	</tr>
	
</table>

<hr/>

<div id="result" class="ui-technical-result"> </div>

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
