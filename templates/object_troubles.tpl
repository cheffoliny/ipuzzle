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

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="nIDTrouble" name="nIDTrouble" value="0" />

<table class="search" style="width:100%;">
	<tr>
		<td class="header_buttons" style="height: 33px;">
		<span id="head_window">Проблеми при обект {$object}</span> 
		</td>
	</tr>
		
	<tr>
		<td id="filter_result">
		
	<!-- начало на работната част -->
	
		<table class="page_data">
			<tr>
			<td style="width: 280px; text-align: left; padding: 2px;">
			
				<div class="input-group" style="width: 270px;">
					<span class="input-group-addon-warning"><img src="images/glyphicons/tech.png" style="width: 12px; height: 12px;" title="Неотстранени" /></span>
					<select id="sTroubleType" name="sTroubleType" class="inp150" onChange="load();">
						<option value="all">Всички</option>
						<option value="tech">Технически</option>
						<option value="operativ">Оперативни</option>
					</select>
				
				</div>
				
			</td>
			<td style="text-align: left;">
			
				<input type="checkbox" id="nService" name="nService" class="clear" onClick="load();" />
				Неотстранени
				
			</td>
			</tr>
	  </table>
	
	<hr>
	
{if $mobile}
	{if $cnt>6}
		<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">
	{else}
		<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">
	{/if}
		</div>
{/if}
	
	<div id="result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="off" style="padding: 1px; width: 100%; height: 400px; overflow: auto !important;"></div>	
 	<!-- край на работната част -->
	</td>
</tr>
</table>

<div id="search"  style="padding-top:10px;width:800px;">
	<table class="page_data" >
		<tr>
			<td style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
				&nbsp;
			</td>
			<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				
				<button class="btn btn-xs btn-success" onClick="editTrouble(0);" title="Докладвай проблем към обекта"><i class="fa fa-plus"></i> Проблем</button>
				<button id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори</button>
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
