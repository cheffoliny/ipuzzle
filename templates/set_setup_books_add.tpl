{literal}
<script>
	rpc_debug = true;
	
	function formSubmit() {
		loadXMLDoc2('save', 3);
		return false;
	}	
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onSubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">Регистрация на кочан</div>
		<br />
		
		<fieldset>
			<legend>Диапазон на номера</legend>		
			<table class="input">

				<tr><td style="font-size: 0px; height: 5px;">&nbsp;</td></tr>
				
				<tr>
					<td align="left">&nbsp;&nbsp;Номера от:</td>
					<td align="right">
						<input type="text" name="nNumFrom" id="nNumFrom" style="width: 180px; text-align: right;" onkeypress="formatDigits(event);" />&nbsp;&nbsp;
					</td>
				</tr>
				
				<tr>
					<td align="left">&nbsp;&nbsp;Номера до:</td>
					<td align="right">
						<input type="text" name="nNumTo" id="nNumTo" style="width: 180px; text-align: right;" onkeypress="formatDigits(event);" />&nbsp;&nbsp;
					</td>
				</tr>
				
				<tr><td style="font-size: 0px; height: 10px;">&nbsp;</td></tr>
			</table>
		</fieldset>
		
		<fieldset>
			<legend>Бележка</legend>	
			<table class="input">
			<tr>
				<td align="left">
					&nbsp;&nbsp;<textarea id="note" name="note" style="width: 265px; height: 20px;"></textarea>	
				</td>
			</tr>
			
			<tr><td style="font-size: 0px; height: 5px;">&nbsp;</td></tr>
			
			</table>
		</fieldset>
		
		<input type="checkbox" id="busy" name="busy" class="clear" /> <span style="color: red">Заключване на диапазона!</span>
				
		<br />
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" onClick="formSubmit()" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>