{literal}
<script>
	//rpc_debug = true;
	
	function editShifts(id) {
		var obj = document.getElementById('nID').value;
		dialogSetSetupObjectShifts( id, obj )
	}

	function shiftHistory(id) {
		dialogShiftHistory( id )
	}

	function delShifts(id) {
		if ( confirm('Наистина ли желаете да премахнете записа?') ) {
			$('nIDShift').value = id;
			loadXMLDoc2('delete', 1);
		}
	}

	function openSchedule() {
		var nID = document.getElementById('nID').value;
		
		window.opener.location.href = 'page.php?page=person_schedule&nIDSelectObject=' + nID;
		window.close();
	}	

	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}	
</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="nIDShift" name="nIDShift" value="0" />

<table class="search" style="width:100%;">
	<tr>
		<td class="header_buttons">
		<span id="head_window">Видове смени за обект {$object}</span> 
		<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>
		{include file=object_tabs.tpl}
		</td>
	</tr>

	<tr class="odd">
		<td id="filter_result">
		{*{if $mobile}*}
			{*{if $cnt>6}*}
				{*<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">*}
			{*{else}*}
				{*<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">*}
			{*{/if}*}
		{*{/if}*}
		
		<!-- начало на работната част -->
		
	  <table class="page_data">
		<tr>
			<td valign="top" style="text-align: right; width: 800px; padding: 2px;">
					
			<button class="btn btn-xs btn-success" onClick="editShifts(0);"><i class="fa fa-plus"></i> Нова смяна</button>
			
			</td>
		</tr>
	</table>
	
	<hr>

	<div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width: 780px; height: 360px;overflow: auto;"></div>
	
 	<!-- край на работната част -->
	</td>
</tr>
</table>

<div id="search"  style="padding-top:10px;width:800px;">
	<table class="page_data" >
		<tr valign="top">
			<td>
				&nbsp;
			</td>
			<td style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				<button id="b100" class="btn btn-xs btn-primary" onClick="openSchedule();"><img src="images/glyphicons/list.png" style="width: 14px; height: 14px;"> График </button>
				<button id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>
			</td>
		</tr>
	</table>
</div>
<div id="NoDisplay" style="display:none"></div>
</form>



<script>
	loadXMLDoc2('result');
	
	{if !$edit.object_shifts_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for (i=0;i<form.elements.length-1;i++) {
				{/literal}{if $person_schedule}{literal}
				if ( (typeof(form.elements[i].id) != undefined) && (form.elements[i].id != 'schedule') ) {
					form.elements[i].setAttribute('disabled','disabled');
				}
				{/literal}{else}
				form.elements[i].setAttribute('disabled','disabled');
				{/if}{literal}
			}

			if ( typeof(form.schedule) != undefined ) {
				form.schedule.setAttribute('enabled','enabled');
			}  
			
		}{/literal}
	{/if}
	
</script>
