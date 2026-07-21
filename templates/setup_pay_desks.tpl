<script>
	{literal}
		rpc_debug = true;
		
		function openPayDesk( id )
		{
			dialogSetSetupPayDesk( 'id=' + id, id );
		}
		
		function deletePayDesk( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
	{/literal}
</script>

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Касови апарати</td>
			<td class="buttons">
				<button onclick="openPayDesk( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter ui-nomenclature-filter-wide">
			<tr>
				<td align="right">Фирма:&nbsp;</td>
				<td align="left">
					<select class="default form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'loadOffices' );" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион:&nbsp;</td>
				<td align="left">
					<select class="default form-control" name="nIDOffice" id="nIDOffice" onchange="loadXMLDoc2( 'loadPersons' );" />
				</td>
				
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Служител:&nbsp;</td>
				<td align="left">
					<select class="default form-control" name="nIDPerson" id="nIDPerson" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Номер:&nbsp;</td>
				<td align="left">
					<input type="text" class="inp150 form-control" id="sNum" name="sNum" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">
					<button name="Button" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button>
				</td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2( 'result' );
</script>
