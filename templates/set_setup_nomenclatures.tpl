<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nType" name="nType" value="{$nType}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} номенклатура</div>
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="200">Наименование:</td>
				<td>
					<input type="text" name="sName" id="sName" class="inp250" />
				</td>
			</tr>
			<tr class="even">
				<td width="200">Единица за Измерване:</td>
				<td>
					<select name="sUnit" id="sUnit" class="select100" />
				</td>
			</tr>
			<tr class="odd">
				<td width="200">Цена:</td>
				<td>
					<input type="text" name="nPrice" id="nPrice" class="inp100" onkeypress="return formatMoney(event);" />
				</td>
			</tr>
			<tr class="odd">
				<td width="200">Тип на Номенклатура:</td>
				<td>
					<select name="nIDNomenclatureType" id="nIDNomenclatureType" class="select250" />
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
	loadXMLDoc2('load');
</script>