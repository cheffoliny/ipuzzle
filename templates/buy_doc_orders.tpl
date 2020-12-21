{literal}

	<script>
		rpc_debug = true;
		
		function onInit() {
			
			if($('sDocStatus').value == 'canceled') {
				$('page_caption').style.backgroundColor = 'red';
			}
			
			if($('sDocStatus').value == 'final') {
				$('b_order').disabled = false;
			}
			
			loadXMLDoc2('result');
		}
		
		function openOrder(id) {
			var sParams = "id=" + id + "&doc_type=buy&id_doc=" + $('nID').value;
			dialogOrder(sParams);
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<input type="hidden" id="sDocStatus" name="sDocStatus" value="{$sDocStatus}">
	
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
			<td class="page_name" valign="top">
				Ордери
			</td>
			<td>
				&nbsp;
			</td>
			<td align="right" width="220px;">
				<table class="input">
					<tr>
						<td style="background-color:#ccffcc;width:100px;padding-left:10px;">
							Сума:
						</td>
						<td style="background-color:#eeeeee;width:100px;">
							<input type="text" id="total_sum" name="total_sum"  class="clear" style="text-align:right;font-weight:bold;width:100px;" readonly>
						</td>
					</tr>
					<tr>
						<td style="background-color:#ccffcc;width:100px;padding-left:10px;">
							Погасена:
						</td>
						<td style="background-color:#eeeeee;width:100px">
							<input type="text" id="orders_sum" name="orders_sum" class="clear" style="text-align:right;font-weight:bold;width:100px;" readonly>
						</td>
					</tr>
					<tr>
						<td style="background-color:#ccffcc;width:100px;padding-left:10px;">
							Непогасена:
						</td>
						<td style="background-color:#eeeeee;width:100px;">
							<input type="text" id="rest_sum" name="rest_sum" class="clear" style="text-align:right;font-weight:bold;width:100px;" readonly>
						</td>
					</tr>
				</table>
			</td>
			<td align="right" valign="top" style="width:150px;">
				<button id="b_order" onclick="openOrder(0);" disabled><img src="images/confirm.gif">Ордер</button>
			</td>
		</tr>
	
	</table>
	<hr>
	<div id="result"></div>
</form>

<script>
	onInit();
</script>