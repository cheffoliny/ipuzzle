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

<form id="form1" action="" class="ui-nomenclature-list" onsubmit="return false;">

	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">
				Номенклатури фирми
			</td>
		</tr>
	</table>
	<center class="ui-nomenclature-filter-wrap">
	<table class="input table-secondary ui-nomenclature-filter">
		<tr>
			<td align="right">
				Фирма	
			</td>
			<td style="width:200px;">
				<select name="nIDFirm" id="nIDFirm" class="form-control" onchange="loadXMLDoc2('result');"></select>
			</td>
			<td>
				<button onclick="openEarexp();" title="Редакция на номенклатурите"><span class="ui-icon ui-icon-edit" aria-hidden="true"></span></button>
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
