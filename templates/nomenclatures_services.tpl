{literal}
	<script>
		rpc_debug = true;
	
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function delService(id) {
			$('id_to_del').value = id;
			if(confirm('Наистина ли желаете да премахнете услугата')) {
				loadXMLDoc2('delete',1);
			}
		}
		
		function setService(id) {
			dialogNomenclatureService(id);
		}
	</script>
{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="id_to_del" name="id_to_del" >	

	<table class="input">
		<tr>
			<td class="page_name">
				Номенклатури услуги
			</td>
			<td align="right">
				<button onclick="setService(0);"><img src="images/confirm.gif">Добави</button>
			</td>
		</tr>
	</table>
	<hr>
	<div id="result"></div>
</form>

<script>
	onInit();
</script>