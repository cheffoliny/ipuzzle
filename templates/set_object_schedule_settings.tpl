{literal}
	<script>
		rpc_debug = true;
		
		function formSubmit()
		{
			loadXMLDoc2( 'save', 3 );
		}
	</script>
{/literal}

<div class="content">
	<form method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}" />
		
		<div class="page_caption">{if $nID}Редактиране на Настройки{else}Нова Настройка{/if}</div>
		<fieldset>
			<legend>Смени - настройки:</legend>
			<table class="input">
			
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
				
				<tr class="even">
					<td align="right" width="150">Коефицент:&nbsp;</td>
					<td align="left"><input type="text" name="nFactor" id="nFactor" style="width: 60px; text-align: right;" onkeypress="return formatMoney( event );" maxlength="6" /></td>
				</tr>
				
				<tr class="odd">
					<td align="right">Начален час:&nbsp;</td>
					<td align="left"><input type="text" name="sNightFrom" id="sNightFrom" style="width: 60px; text-align: right;" onkeypress="return formatTime( event );" maxlength="6" />&nbsp;ч.</td>
				</tr>

				<tr class="even">
					<td align="right">Краен час:&nbsp;</td>
					<td align="left"><input type="text" name="sNightTo" id="sNightTo" style="width: 60px; text-align: right;" onkeypress="return formatTime( event );" maxlength="6" />&nbsp;ч.</td>
				</tr>
				
				<tr class="odd"><td colspan="2" style="height: 8px;"></td></tr>
			
			</table>
		</fieldset>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="button" onClick="formSubmit();" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>

	</form>
</div>

<script>
	loadXMLDoc2( 'load' );
</script>