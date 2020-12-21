{literal}

	<script>
		rpc_debug = true;
		
		function onInit() {
			
			if($('sDocStatus').value == 'canceled') {
				$('page_caption').style.backgroundColor = 'red';
			}
			
			if($('sDocStatus').value != 'proforma') {
				$('b_edit_row').style.display = 'none';
			}
			
			loadXMLDoc2('result');
		}
		
		function delRow(id) {
			$('id_row_to_del').value = id;
			loadXMLDoc2('del_row',1);
			
		}
		
		function editRow(id) {
			var id_buy_doc = $('nID').value;
			dialogBuyDocRow(id,id_buy_doc);
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<input type="hidden" id="sDocStatus" name="sDocStatus" value="{$sDocStatus}">
	<input type="hidden" id="id_row_to_del" name="id_row_to_del" value="">
	
	<div class="page_caption" id="page_caption">{$sPageCaption|default:''}</div>

	<table width="100%" cellpadding="0" cellspacing="0" id="filter">
		<tr>
			<td>
				{include file='buy_doc_tabs.tpl'}
			</td>
		</tr>
	</table>
	
	<table class="input">
		<tr>
			<td class="page_name">
				Опис
			</td>
			<td align="right">
				<button id="b_edit_row" onclick="editRow(0);"><img src="images/confirm.gif">Нов ред</button>
			</td>
		</tr>
	
	</table>
	<hr>
	<div id="result"></div>
</form>

<script>
	onInit();
</script>