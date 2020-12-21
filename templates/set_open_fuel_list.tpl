{literal}
<script>
	rpc_debug=true;
	
	function formSubmit() {
		loadXMLDoc2('save', 3);
	}
</script>
{/literal}

<div class="content">
	<form method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}" />
		
		<div class="page_caption">Отваряне на горивен лист</div>

		<fieldset>
			<legend>Данни за зарядка:</legend>
			<table class="input">
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
				<tr class="even">
					<td width="130">Гориво в литра:</td>
					<td><input type="text" name="nFuelLiter" id="nFuelLiter" style="width: 128px; text-align: right;" onkeypress="return formatMoney(event);" maxlength="6" /></td>
				</tr>
				<tr class="even">
					<td width="130">Пробег км:</td>
					<td><input type="text" name="nKm" id="nKm" style="width: 128px; text-align: right;" onkeypress="return formatDigits(event);" maxlength="6" /></td>
				</tr>
				<tr class="even">
					<td>Гориво в лева:</td>
					<td><input type="text" name="nFuelPrice" id="nFuelPrice" style="width: 128px; text-align: right;" onkeypress="return formatMoney(event);" maxlength="6" /></td>
				</tr>
				<tr class="even">
					<td>№ на фактура:</td>
					<td><input type="text" name="sFuelInvoice" id="sFuelInvoice" style="width: 128px; text-align: right;" /></td>
				</tr>
				<tr class="odd"><td colspan="2" style="height: 8px;"></td></tr>				
			</table>
		</fieldset>

		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="button" onClick="formSubmit();" class="search"> Отвори </button>
					<button type="button" onClick="parent.window.close();"> Откажи </button>
				</td>
			</tr>
		</table>

	</form>
</div>