{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('load');
		}
	</script>
{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<div class="page_caption">{if $nID}Редактиране{else}Добавяне{/if} на номенклатура услуга</div>

	<table class="input" style="margin-top:20px;" border="0">
		<tr>
			<td align="right" style="padding-bottom:5px;">
				Код
			</td>
			<td style="padding-bottom:5px;width:100px;">
				<input type="text" name="sCode" id="sCode" style="width:80px;">
			</td>
			<td align="right" style="padding-bottom:5px;">
				Име
			</td>
			<td style="padding-bottom:5px;">
				<input type="text" name="sName" id="sName" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td align="right">
				Цена
			</td>
			<td>
				<input type="text" name="price" id="price" style="width:100px;" >
			</td>
			<td>
				&nbsp;
			</td>
			<td colspan="1" rowspan="3" style="width:100px;">
				<fieldset>
				<legend>Позволяват се корекции за:</legend>
				<table class="input">	
					<tr>
						<td align="right">
							<input type="checkbox" class="clear" name="name_edit" id="name_edit">
						</td>
						<td>
							Име
						</td>
					</tr>
					<tr>
						<td align="right">
							<input type="checkbox" class="clear" name="quantity_edit" id="quantity_edit">
						</td>
						<td colspan="3">
							Количество
						</td>
					</tr>
					<tr>
						<td align="right">
							<input type="checkbox" class="clear" name="price_edit" id="price_edit">
						</td>
						<td>
							Цена
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align="right">
				Тип
			</td>
			<td>
				<select id="type_service" name="type_service" style="width:100px;">
					<option value="month">месечна</option>
					<option value="single">еднократна</option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">
				Мярка
			</td>
			<td>
				<select id="nIDMeasure" name="nIDMeasure" style="width:100px;"></select>
			</td>
		</tr>
		<tr>
			<td align="right" colspan="2" style="padding-top:10px;">
				Номенклатура-приход
			</td>
			<td colspan="2" style="padding-top:10px;">
				<select id="nIDNomenclatureEarning" name="nIDNomenclatureEarning" style="width:230px;"></select>
			</td>
		</tr>
		<tr>
			<td style="padding-top:20px; text-align: right;">
				<input type="checkbox" class="clear" name="for_trans" id="for_trans" />
			</td>
			<td style="padding-top:20px; width: 85px;">
				ТРАНСФЕР
			</td>
						
			<td colspan="2" style="padding-top:20px;" align="right">
				<button onclick="loadXMLDoc2('save',3);"><img src="images/confirm.gif">Запиши</button>
				<button onclick="window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
		
	</table>	

</form>

<script>
	onInit();
</script>