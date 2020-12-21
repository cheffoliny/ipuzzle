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
	
	<div class="page_caption">{if $nID}Редактиране{else}Добавяне{/if} на номенклатура приход</div>
	
	<table class="input" style="margin-top:20px;">
		<tr>
			<td align="right">
				Код
			</td>
			<td>
				<input type="text" id="sCode" name="sCode" style="width:50px;">
			</td>
			<td align="right">
				Системен
			</td>
			<td>
				<input type="checkbox" class="clear" id="is_system" name="is_system">
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