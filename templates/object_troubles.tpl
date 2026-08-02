{literal}
<script>
	rpc_debug = true;
	

	function delTrouble(id) {
		if ( confirm('Наистина ли желаете да премахнете записа?') ) {
			$('nIDTrouble').value = id;
			loadXMLDoc2('delete', 1);
		}
	}

	function load() {
		loadXMLDoc2('result');
	}
		
	function editTrouble(id) {
		var obj = document.getElementById('nID').value;
		dialogSetSetupTrouble( id, obj )
	}
	
</script>
{/literal}

<form name="form1" id="form1" class="ui-object-core ui-object-troubles" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="nIDTrouble" name="nIDTrouble" value="0" />

<table class="search ui-object-troubles-shell">
	<tr>
		<td class="header_buttons">
		<span id="head_window">Проблеми при обект {$object}</span> 
		</td>
	</tr>
		
	<tr>
		<td id="filter_result">
		
	<!-- начало на работната част -->
	
		<table class="page_data ui-object-troubles-filter">
			<tr>
			<td style="width: 280px; text-align: left; padding: 2px;">
			
				<div class="input-group">
					<span class="input-group-addon-warning" title="Неотстранени"><span class="ui-icon ui-icon-settings" aria-hidden="true"></span></span>
					<select id="sTroubleType" name="sTroubleType" class="inp150 form-control" onChange="load();">
						<option value="all">Всички</option>
						<option value="tech">Технически</option>
						<option value="operativ">Оперативни</option>
					</select>
				
				</div>
				
			</td>
			<td style="text-align: left;">
			
				<input type="checkbox" id="nService" name="nService" class="clear ui-nomenclature-checkbox" onClick="load();" />
				Неотстранени
				
			</td>
			</tr>
	  </table>
	
	<hr>
	
	<div id="result" class="ui-object-result ui-object-troubles-result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="off"></div>
 	<!-- край на работната част -->
	</td>
</tr>
</table>

<div class="fixed-bottom ui-object-troubles-actions ui-object-actions">
	<table class="page_data ui-nomenclature-actions" >
		<tr>
			<td style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
				&nbsp;
			</td>
			<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				
				<button type="button" class="btn btn-xs btn-success" onClick="editTrouble(0);" title="Докладвай проблем към обекта"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Проблем</button>
				<button type="button" id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
			</td>
		</tr>
	</table>
</div>

<div id="NoDisplay" style="display:none"></div>
</form>



<script>
	loadXMLDoc2('result');
	
	{if !$edit.object_troubles_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}	
</script>
