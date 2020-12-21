<script>
{literal}
	rpc_debug = true;
{/literal}
</script>


<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<div class="page_caption">Разпращане на фактури по email - ИСТОРИЯ</div>
	<table cellspacing="0" cellpadding="0" width="100%" id="filter">
		<tr>
			<td>{include file=finance_instruments_tabs.tpl}</td>
		</tr>
	</table>

	<br />
	
	<div id="result" rpc_excel_panel="on" rpc_paging="on" rpc_resize="on" style="overflow: auto;"></div>

	</form>
	
<script>
	loadXMLDoc2('result');
</script>