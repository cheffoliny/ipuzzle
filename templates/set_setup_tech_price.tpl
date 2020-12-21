<script>
	rpc_debug = true;
</script>

<dlcalendar click_element_id="editPriceListDate" input_element_id="sPriceListDate" tool_tip="Изберете дата"></dlcalendar>
<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция{else}Добавяне{/if}</div>
		<br />
		
		<table class="input">
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
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="editPriceListDate" />
				</td>
			</tr>
		</table>
		
		<br />
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
	loadXMLDoc2('get');
</script>