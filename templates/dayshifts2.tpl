{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('load');
		}
		
		function openShift( id ) {
			var sParams = new String();
			
			if( id ) {
				sParams = 'nID=' + id;
				dialogObjectDuty( sParams );
			}
		}
	
		function autoValidate() {
			// Pavel - Avtomatichni smeni
			$('Validate').onclick = function() {};
			loadXMLDoc2('autoValidate');		
		}
	
	</script>

	<style>
		table.result td.red a:link,
		table.result td.red a:visited {
			color:#FF6464;
			text-decoration: none;
			font-weight : bold;
		}
	</style>
{/literal}
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="first" id="first" value="1">
	<table class = "page_data">
		<tr>
			<td class="page_name">Смени</td>
		
			<td valign="top" align="right" width="160px" >
			{if $auto_schedule}
				<button type="button" name="Validate" onClick="autoValidate(); return false;" class="search"><img src="images/reload.gif">Валидация</button>
			{/if}
			</td>
		
		</tr>		
	</table>
	<center>
		<table class="search">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" />
				</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc2('result');"><img src="images/confirm.gif">Опресни</button></td>
			</tr>
		</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		onInit();
		rpc_on_exit = function ( nCode )
		{
			if( !parseInt( nCode ) )
			{
				if($('first').value == "1") {
					$('first').value = "0";
					loadXMLDoc2('result');
				} else {
					setTimeout( "loadXMLDoc2('result')", 300000 );
				}
			}
		}
		
		loadXMLDoc2('result');
	</script>
{/literal}