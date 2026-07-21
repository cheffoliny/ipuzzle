<script>
	rpc_debug = true;
</script>

<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-financial-dialog" onsubmit="loadXMLDoc2( 'save', 3 ); return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} отстъпка</div>
		<br />
		
		<table class="input ui-nomenclature-form">
			<tr class="odd">
				<td align="left">Наименование:</td>
				<td align="left">
					<input type="text" name="sName" id="sName" class="inp250" />
				</td>
			</tr>
			<tr class="odd">
				<td align="left">Услуга:</td>
				<td align="left">
					<select id="nIDNomenclatureEarning" name="nIDNomenclatureEarning" class="select250" />
				</td>
			</tr>
			<tr>
				<td align="left">Брой месеци:</td>
				<td align="left">
					<input type="text" name="nMonthsCount" id="nMonthsCount" class="inp75" onkeypress="formatDigits( event );" />
				</td>
			</tr>
			<tr>
				<td align="left">Процент:</td>
				<td align="left">
					<input type="text" name="nPercent" id="nPercent" class="inp75" onkeypress="formatDigits( event );" />&nbsp;%
				</td>
			</tr>
		</table>
		
		<br />
		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2( 'get' );
</script>
