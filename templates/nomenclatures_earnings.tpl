{literal}
	<script>
		rpc_debug = true;
	
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function delNomenclatureEarning(id) {
			$('id_to_del').value = id;
			if(confirm('Наистина ли желаете да изтриете номенклатурата приход')) {
				loadXMLDoc2('delete',1);
			}
		}
		function openNomenclatureEarning(id) {
			dialogNomenclatureEarning(id);
		}
		
	</script>
{/literal}


<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="id_to_del" name="id_to_del" value="0">
	<table class="input">
		<tr>
			<td class="page_name">
				Номенклатури приходи
			</td>
			<td align="right">
				<button onclick="openNomenclatureEarning(0);"><img src="images/confirm.gif">Добави</button>
			</td>
		</tr>
	</table>
	<hr>
	<div id="result"></div>
</form>

<script>
	onInit();
</script>