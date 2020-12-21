{literal}
	<script>
		//rpc_debug = true;
		
		function submitForm() {
			loadXMLDoc2('save',3);
		}
		
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} смяна</div>

		<table class="input">
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			<tr class="even">
				<td width="100">Код:</td>
				<td>
					<input type="text" name="sCode" id="sCode" style="width: 80px;" />
				</td>
			</tr>
			<tr class="odd">
				<td width="100">Наименование:</td>
				<td>
					<input type="text" name="sName" id="sName" style="width: 240px;" />
				</td>
			</tr>
			<tr class="even">
				<td>Период:</td>
				<td>
					<table width="100%" border="0" class="input" cellspacing="0" colspacing="0"><tr>
						<td align="left">от: </td><td align="left"><input type="text" name="sShiftFrom" id="sShiftFrom" style="width: 60px;" onKeyPress="return formatTime(event);" maxlength="6" />&nbsp;ч.</td>
						<td align="right">до: </td><td align="right"><input type="text" name="sShiftTo" id="sShiftTo" style="width: 60px;" onKeyPress="return formatTime(event);" maxlength="6" />&nbsp;ч.</td>
					</tr></table>
				</td>
			</tr>
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
		</table>
		
		<fieldset>
			<legend>Допълнителна информация</legend>
			<table class="input">
				<tr class="even">
					<td align="center">
						<textarea name="sDescription" id="sDescription" style="width: 325px; height: 80px;" /></textarea>
					</td>
				</tr>
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			</table>
		</fieldset>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" onclick="submitForm();" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>