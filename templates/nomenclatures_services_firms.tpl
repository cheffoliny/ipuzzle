{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function editFirmServices() {
			id = $('nIDFirm').value;
			dialogNomenclaturesServicesFirms(id);
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">

	<table class="input">
		<tr>
			<td class="page_name">
				Услуги фирми
			</td>
		</tr>
	</table>
	<center>
	<table class="input">
		<tr>
			<td align="right">
				Фирма	
			</td>
			<td style="width:200px;">
				<select name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('result');"></select>
			</td>
			<td>
				<button onclick="editFirmServices();" style="width:20px;"><img src="images/edit.gif"></button>
			</td>
		</tr>
	</table>
	</center>
	<hr>
	
	<div id="result"></div>
</form>

<script>
	onInit();
</script>