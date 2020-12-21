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

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data">
		<tr>
			<td class="page_name">Касови апарати</td>
			<td class="buttons">
				<button onclick="openPayDesk( 0 );"><img src="images/plus.gif"> Добави </button>
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Фирма:&nbsp;</td>
				<td align="left">
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'loadOffices' );" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион:&nbsp;</td>
				<td align="left">
					<select class="default" name="nIDOffice" id="nIDOffice" onchange="loadXMLDoc2( 'loadPersons' );" />
				</td>
				
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Служител:&nbsp;</td>
				<td align="left">
					<select class="default" name="nIDPerson" id="nIDPerson" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Номер:&nbsp;</td>
				<td align="left">
					<input type="text" class="inp150" id="sNum" name="sNum" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">
					<button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif">Търси</button>
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