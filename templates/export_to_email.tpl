{literal}
	<script>
		rpc_debug = true;
	
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function delEmail(id) {
			$('nID').value = id;
			
			if (confirm('Наистина ли желаете да изтриете този e-mail') ) {
				loadXMLDoc2('delete', 1);
			}
		}
		
		function editEmail(id) {
			dialogExportToEmail(id);
		}
		
	</script>
{/literal}


<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0">
	<table class="input">
		<tr>
			<td class="page_name">
				Регистрирани имейл адреси за уводомяване при промяна на експортирани документи
			</td>
			<td align="right">
				<button onclick="editEmail(0);"><img src="images/confirm.gif">Добави</button>
			</td>
		</tr>
	</table>
	<hr>
	<div id="result"></div>
</form>

<script>
	onInit();
</script>