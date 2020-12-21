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
	
	<div class="page_caption">{if $nID}Редактиране{else}Добавяне{/if} на номенклатура разход</div>
	
	<table class="input" style="margin-top:20px;" >
		<tr>
			<td align="right">
				Код
			</td>
			<td colspan="3">
				<input type="text" id="sCode" name="sCode" style="width:50px;">
			</td>
		</tr>
		<tr>
			<td align="right">
				Име
			</td>
			<td colspan="3">
				<input type="text" id="sName" name="sName" style="width:250px;">
			</td>
		</tr>
		<tr>
			<td align="right" style="padding-top:10px;">
				<input type="checkbox" class="clear" name="for_salary" id="for_salary">
			</td>
			<td style="padding-top:10px; width: 70px;">
				За Заплати
			</td>
			
			<td style="padding-top:10px; text-align: right;">
				<input type="checkbox" class="clear" name="for_trans" id="for_trans" />
			</td>
			<td style="padding-top:10px; width: 85px;">
				ТРАНСФЕР
			</td>			
		</tr>
		<tr>
			<td align="right">
				<input type="checkbox" class="clear" name="for_gsm" id="for_gsm">
			</td>
			<td colspan="3">
				За GSM-Mtel
			</td>
		</tr>
		<tr>
			<td align="right">
				<input type="checkbox" class="clear" name="for_dds" id="for_dds">
			</td>
			<td colspan="3">
				За ДДС
			</td>
		</tr>
		<tr>
			<td colspan="4" align="right" style="padding-top:20px;">
				<button onclick="loadXMLDoc2('save',3);"><img src="images/confirm.gif">Запиши</button>
				<button onclick="window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
	
	</table>
</form>

<script>
	onInit();
</script>