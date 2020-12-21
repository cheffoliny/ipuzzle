{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function openEarexp() {
			id = $('nIDFirm').value;
			dialogNomenclaturesEarexpFirms(id);
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">

	<table class="input">
		<tr>
			<td class="page_name">
				Номенклатури фирми
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
				<button onclick="openEarexp();" style="width:20px;"><img src="images/edit.gif"></button>
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