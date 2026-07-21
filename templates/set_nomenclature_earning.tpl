{literal}
	<script>
	
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('load');
		}
		
	</script>

{/literal}

<form id="form1" action="" class="ui-nomenclature-dialog ui-money-nomenclature-dialog" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<div class="page_caption">{if $nID}Редактиране{else}Добавяне{/if} на номенклатура приход</div>
	
	<table class="input ui-nomenclature-form" style="margin-top:20px;">
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
				<input type="checkbox" class="clear ui-nomenclature-checkbox" id="is_system" name="is_system">
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
			<td colspan="4" align="right" class="ui-inline-dialog-actions" style="padding-top:20px;">
				<button onclick="loadXMLDoc2('save',3);"><span class="ui-icon ui-icon-save" aria-hidden="true"></span>Запиши</button>
				<button onclick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
			</td>
		</tr>
	
	</table>
</form>

<script>
	onInit();
</script>
