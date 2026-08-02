<script>
{literal}
	rpc_debug = true;
	rpc_html_debug = true;
		
	function formSubmit() {
		loadXMLDoc2('result');
	}
	
	function openSaleDoc(id) {
		dialogSaleDocInfo2(id);
	}	
{/literal}
</script>

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />

	<div class="page_caption" id="capt" name="capt">ФАКТУРИРАНЕ</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
		<tr>
			<td>{include file="finance_operations_tabs.tpl"}</td>
		</tr>
	</table>
	
	<table class="page_data" style="width: 100%">
		<tr style="font-size: 0px; height: 5px;"><td></td></tr>
		
		<tr>
			<td colspan="2" align="center">
				
				<table class="input" style="text-align: center;" >

					<tr>

						<td align="right">
						{if $invoicement}
							<button type="button" id="btnRecord" name="btnRecord" class="ui-final-action" onClick="formSubmit(); return false;" ><span class="ui-icon ui-icon-document" aria-hidden="true"></span>Фактуриране</button>
						{/if}
						</td>
					</tr>
					
				</table>
				
			</td>
		</tr>
	</table>
	<hr />

	<div id="result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="on" style="overflow: auto;"></div>

	</form>
	
<script>
	//loadXMLDoc2('result');
</script>
