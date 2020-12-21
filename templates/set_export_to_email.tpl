{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('load');
		}
		
		function save() {
			loadXMLDoc2('save', 3);
		}		
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<div class="page_caption">{if $nID}Редактиране{else}Добавяне{/if} на имейл</div>
	
	<table class="input" style="margin-top:20px;">

		<tr>
			<td align="right">
				email:
			</td>
			<td align="right" style="padding-right: 10px;">
				<input type="text" id="sName" name="sName" style="width: 250px; text-align: left; color: blue;">
			</td>
		</tr>
		
		<tr>
			<td colspan="2" align="right" style="padding-top: 10px; padding-right: 10px;">
				<button onclick="save(); return false;"><img src="images/confirm.gif">Запиши</button>
				<button onclick="window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
	
	</table>
</form>

<script>
	onInit();
</script>