<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} цена за Отговорност</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td width="120">Тип:</td>
				<td>
					<select name="sType" id="sType" class="select200">
						<option value='mdo'>Месечна Денонощна Охрана</option>
						<option value='tp'>ПЛАН</option>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td width="120">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr class="even">
				<td width="120">Годишна:</td>
				<td>
					<input type="text" name="nYearly" id="nYearly" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
				</td>
			</tr>
			<tr class="odd">
				<td width="120">Еднократна:</td>
				<td>
					<input type="text" name="nSingle" id="nSingle" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
				</td>
			</tr>
			<tr class="even">
				<td width="120">Коефициент:</td>
				<td>
					<input type="text" name="nFactor" id="nFactor" class="inp50" onkeypress="return formatMoney(event);" />
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