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
<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-schedule-report ui-day-shifts-report" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="first" id="first" value="1">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Смени</td>
		
			<td valign="top" align="right" width="160px" >
			{if $auto_schedule}
				<button type="button" id="Validate" name="Validate" onClick="autoValidate(); return false;" class="search"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span>Валидация</button>
			{/if}
			</td>
		
		</tr>		
	</table>
	<center class="ui-nomenclature-filter-wrap ui-schedule-filter-wrap">
		<table class="search ui-nomenclature-filter ui-schedule-filter">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" />
				</td>
				<td align="right"><button type="button" name="Button" onclick="loadXMLDoc2('result');"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span>Опресни</button></td>
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
