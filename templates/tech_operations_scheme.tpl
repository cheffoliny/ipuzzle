{literal}
	<script>
		rpc_debug = true;
		
		function getResult()
		{
			loadXMLDoc2( 'result' );
		}
	</script>
	
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-tech-operations-scheme" onSubmit="return false;">
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Шаблон за използване при изграждане от ел. договор</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
</form>

<script>
	getResult();
</script>
