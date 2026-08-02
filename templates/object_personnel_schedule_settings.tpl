{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		function editScheduleSettings( id ) {
			dialogScheduleSettings( id );
		}
		
	</script>
{/literal}

<form name="form1" id="form1" class="ui-object-core ui-object-personnel-settings" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	
	<table class="page_data ui-object-personnel-settings-title">
		<tr>
			<td class="page_name">Персонален график - НАСТРОЙКИ</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result" class="ui-object-result ui-object-personnel-settings-result"
		rpc_excel_panel="off"
		rpc_resize="off"
		rpc_paging="off"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>
