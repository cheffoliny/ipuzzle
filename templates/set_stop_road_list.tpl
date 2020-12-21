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
		
		<div class="page_caption">Деактивиране на пътен лист</div>
		<fieldset>
			<legend>Данни за обхода:</legend>
			<table class="input">
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
				<tr class="even">
					<td width="130">Начален километраж:</td>
					<td><input type="text" name="startKm" id="startKm" style="width: 128px; text-align: right;" readonly /></td>
				</tr>
				<tr class="even">
					<td width="130">Краен километраж:</td>
					<td><input type="text" name="endKm" id="endKm" style="width: 128px; text-align: right;" onkeypress="return formatDigits(event);" maxlength="7" /></td>
				</tr>
				<tr class="odd"><td colspan="2" style="height: 8px;"></td></tr>
			</table>
		</fieldset>

		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="button" onClick="formSubmit();" class="search"> Деактивирай </button>
					<button type="button" onClick="parent.window.close();"> Откажи </button>
				</td>
			</tr>
		</table>

	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>