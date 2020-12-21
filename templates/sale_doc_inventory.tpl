{literal}

	<script>
		rpc_debug = true;
		
		function onInit() {
			
			if($('sDocStatus').value == 'canceled') {
				$('page_caption').style.backgroundColor = 'red';
			}
			
			loadXMLDoc2('result');
		}
		
		function just_do_it() {
			
			switch($('sel').value) {
				case '1':
					checkAll( true );
				break;
				case '2':
					checkAll( false );
				break;	
			}
		}
		
		function izvestie() {
			loadXMLDoc2('izvestie');
			
			rpc_on_exit = function () {
				var nNewID = $('id_new_sale_doc').value;
				if(nNewID != '0') {
					dialogSaleDocInfo2(nNewID);
				}
				
				$('id_new_sale_doc').value = '0';
			}
		}
		
		function openObject(id_object) {
			var sParams = "nID=" + id_object
			dialogObjectInfo(sParams);
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<input type="hidden" name="id_new_sale_doc" id="id_new_sale_doc" value="0">
	<input type="hidden" name="sDocStatus" id="sDocStatus" value="{$sDocStatus}">
	
	<div class="page_caption" id="page_caption">{$sPageCaption|default:''}</div>

	<table width="100%" cellpadding="0" cellspacing="0" id="filter">
		<tr>
			<td>
				{include file='sale_doc_tabs.tpl'}
			</td>
		</tr>
	</table>
	<table class="input">
		<tr>
			<td class="page_name">
				Опис
			</td>
			<td align="right">
				<div id="div_button">
					<button onclick="izvestie();"><img src="images/confirm.gif">Кредитно известие</button>
				</div>
			</td>
		</tr>
	</table>
	<hr>
	<div id="result"></div>
	
</form>

<script>
	onInit();
</script>