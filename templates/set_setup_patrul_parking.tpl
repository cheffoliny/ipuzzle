{literal}
<script>
	//rpc_debug = true;
	
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
	
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} стоянка</div>

		<table class="input">
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			<tr class="even">
				<td width="100">Наименование:</td>
				<td>
					<input type="text" name="sName" id="sName" style="width: 240px;" />
				</td>
			</tr>
			<tr class="odd">
				<td width="100">Фирма:</td>
				<td>
					<select name="nIDFirm" id="nIDFirm" style="width: 240px;" onchange="loadXMLDoc2('loadOffices')"></select>
				</td>
			</tr>
			<tr class="even">
				<td width="100">Регион:</td>
				<td>
					<select name="nIDOffice" id="nIDOffice" style="width: 240px;" ></select>
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
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>