{literal}
	<script>
		rpc_debug = true;
		
		function editScheduleSettings( id ) {
			dialogScheduleSettings( id );
		}
		
	</script>
{/literal}

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	
	<table class="page_data">
		<tr>
			<td class="page_name">Персонален график - НАСТРОЙКИ</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result" rpc_excel_panel="off" ></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>