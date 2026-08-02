{literal}
<script>
	rpc_debug=true;
	rpc_html_debug=true;
	
	function formSubmit( id )
		{
			loadXMLDoc2('save', 3);
		}
</script>
{/literal}

<div class="content ui-nomenclature-dialog-shell">
	<form method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-asset-dialog ui-asset-settings-dialog" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}" />
		
		<div class="page_caption">{if $nID}Редактиране на Настройки{else}Нова Настройка{/if}</div>
		<fieldset class="ui-nomenclature-fieldset">
			<legend>Коефициенти: </legend>
			<table class="input ui-nomenclature-form">
				
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
				
				<tr class="even">
					<td align="left" width="150">Коефициент наработка:&nbsp;</td>
					<td align="left"><input type="text" name="sAssetEarningCoef" id="sAssetEarningCoef" style="width: 80px; text-align: right;" onkeypress="return formatDigits(event);" maxlength="3" />&nbsp;%</td>
				</tr>

				<tr class="even">
					<td align="left">Коефициент актив самоучастие:&nbsp;</td>
					<td align="left"><input type="text" name="sAssetOwnCoef" id="sAssetOwnCoef" style="width: 80px; text-align: right;" onkeypress="return formatDigits(event);" maxlength="3" />&nbsp;%</td>
				</tr>

				<tr class="odd"><td colspan="2" style="height: 8px;"></td></tr>				
			
			</table>
		</fieldset>

		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="button" onClick="formSubmit();" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>

	</form>
</div>

<script>
	loadXMLDoc2('load');		
</script>
