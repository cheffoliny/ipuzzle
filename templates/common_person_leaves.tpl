{literal}
	<script>
		rpc_debug = true;
		
		function openPerson( id )
		{
			dialogPersonLeave( id );
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<table class="page_data">
		<tr>
			<td class="page_name">Обща Справка</td>
		</tr>
	</table>
	
	<center>
		<table class="input" style="width: 520px;">
			<tr>
				<td align="right">Фирма:&nbsp;</td>
				<td align="left">
					<select id="nIDFirm" name="nIDFirm" class="select200" onchange="loadXMLDoc2( 'loadOffices' );"/>
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион:&nbsp;</td>
				<td align="left">
					<select id="nIDOffice" name="nIDOffice" class="select200" onchange="loadXMLDoc2( 'loadObjects' );"/>
				</td>
				
				<td colspan="2">&nbsp;</td>
				
			</tr>
			<tr>
				<td align="right">Обект:&nbsp;</td>
				<td align="left">
					<select id="nIDObject" name="nIDObject" style="width: 350px;" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Година:&nbsp;</td>
				<td align="left">
					<input style="width: 40px; text-align: right" onkeypress="return formatDigits( event );" name="nYear" id="nYear" type="text" value="{$year}"/>&nbsp;&nbsp;
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">
					<button onclick="loadXMLDoc2( 'result' );" name="Button"><img src="images/confirm.gif"> Търси </button>
				</td>
			</tr>
		</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		rpc_on_exit = function()
		{
			rpc_on_exit = function() {}
			
			loadXMLDoc2( "result" );
		}
		
		loadXMLDoc2( "load" );
	</script>
{/literal}