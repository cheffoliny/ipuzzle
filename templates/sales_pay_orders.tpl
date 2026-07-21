{literal}

	<script>
		rpc_debug = true;
	
		function onInit() {
			loadXMLDoc2('load');
		}
		
		function sumResto() {
			var nSum = parseFloat($('sum').value);
			var nCashSum = parseFloat($('cash_sum').value);
			
			var nResto = nCashSum - nSum;
			
			nResto = Math.round(nResto * 100)/100;
			nResto = nResto.toFixed(2);
			
			if(nResto >= 0) {
				$('resto').value = nResto;
			} else {
				$('resto').value = '';
			}
		}
		
		function confirm() {
			loadXMLDoc2('confirm',3);
		}
		
	</script>

{/literal}

<form id="form1" action="" class="ui-nomenclature-dialog ui-finance-document-dialog ui-payment-dialog" onsubmit="return false;">
	<input type="hidden" name="sIDs" id="sIDs" value="{$sIDs}">
	<input type="hidden" name="nDDS" id="nDDS" value="{$nDDS}">

	<div class="page_caption">Плащане {if $nDDS}[ОПРОСТЕНА]{/if}</div>
	
	<table class="input" style="margin-top:5px;">
		<tr class="even">
			<td align="right">
				Сума
			</td>
			<td>
				<input type="text" name="sum" id="sum" style="width:60px;text-align:right;" onkeypress="return formatMoney(event)" onkeyup="sumResto();" >&nbsp;€
			</td>
		</tr>
		<tr class="odd">
			<td align="right">
				Сметка
			</td>
			<td>
				<select name="account" id="account"></select>
			</td>
		</tr>
		<tr class="even">
			<td align="right">
				Платено в брой
			</td>
			<td>
				<input type="text" id="cash_sum" name="cash_sum" style="width:60px;text-align:right;" onkeypress="return formatMoney(event)" onkeyup="sumResto();" > €
			</td>
		</tr>
		<tr class="odd">
			<td align="right">
				Ресто
			</td>
			<td>
				<input type="text" id="resto" name="resto" style="width:60px;text-align:right;" readonly> €
			</td>
		</tr>
		<tr>
			<td colspan="2" align="right">
				<br>
				<button type="button" class="search" onclick="confirm();"><span class="ui-icon ui-icon-check" aria-hidden="true"></span>Потвърди</button>
				<button type="button" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
			</td>
		</tr>
		
	</table>
</form>

<script>
	onInit();
</script>
