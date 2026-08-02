<script>
	rpc_debug = true;
</script>

<dlcalendar click_element_id="editPriceListDate" input_element_id="sPriceListDate" tool_tip="Изберете дата"></dlcalendar>
<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-operational-dialog ui-tech-price-dialog" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция{else}Добавяне{/if}</div>
		<br />
		
		<table class="input ui-nomenclature-form">
			<tr class="odd">
				<td width="300">Основна Цена:</td>
				<td>
					<input type="text" name="nBasePrice" id="nBasePrice" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
				</td>
			</tr>
			<tr class="odd">
				<td width="300">Оскъпяване:</td>
				<td>
					<input type="text" name="nFactor" id="nFactor" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
				</td>
			</tr>
			<tr class="odd">
				<td width="300">Дата на последната актуална ценова листа:</td>
				<td>
					<input type="text" name="sPriceListDate" id="sPriceListDate" class="inp100" onkeypress="return formatDate(event, '.');" />
					&nbsp;
					<button type="button" id="editPriceListDate" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Изберете дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
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
	loadXMLDoc2('get');
</script>
