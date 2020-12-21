{literal}
<script>
	rpc_debug = true;

	function dutyPrev() {
		alert(step);
		step = eval(document.getElementById('nOffset').value) - 1;
		document.getElementById('nOffset').value = step;
		loadXMLDoc2('result');
	}
	
	function dutyNext(act) {
		//document.getElementById('sAct').value = act;
		var step = document.getElementById('nStep');
		if ( act == 'prev' ) {
			step.value++;
		} else if ( act == 'next' ) {
			if ( step.value > 0 ) {
				step.value--;
			}
		}

		loadXMLDoc2('result');
		
		rpc_on_exit = function() {
			var step = document.getElementById('nStep').value;
			var butt = document.getElementById('butShift');
			
			if ( step == 1 ) {
				butt.value = '<img src=\"images/cancel.gif\" />Изтрий';
				butt.disabled = false;
			} else if ( step > 1 ) {
				butt.value = '<img src=\"images/cancel.gif\" />Изтрий';
				butt.disabled = true;
			} else {
				butt.value = '<img src=\"images/confirm.gif\" />Смяна';
				butt.disabled = false;
			}
		}
	}	

	function goDuty() {
		var step = document.getElementById('nStep');
		
		if ( step.value == 0 ) {
			loadXMLDoc2('duty', 1);
		} else if ( step.value == 1 ) {
			loadXMLDoc2('erase', 1);
			rpc_on_exit = function() {
				step.value--;
			}
		}
	}	
	
	function openPerson(id) {
		//alert(id);
		var ids = id.split(',');
		dialogPerson(ids[1]);
	}
	
	function goTime() {
		var shift = document.getElementById('sShift');
		var shiftT = document.getElementById('sShiftT');
		
		t = new Date( );
		var tstamp = t.getTime();
		
		var day = new String(t.getDate()).length == 1 ? "0" + t.getDate() : t.getDate();
		var month = new String( parseInt(t.getMonth()+1) ).length == 1 ? "0" + parseInt(t.getMonth()+1) : parseInt(t.getMonth()+1);
		var hour = new String(t.getHours()).length == 1 ? "0" + t.getHours() : t.getHours();
		var minute = new String(t.getMinutes()).length == 1 ? "0" + t.getMinutes() : t.getMinutes();

		var tnowD = day + '.' + month + '.' + t.getFullYear();
		var tnowT = hour + ':' + minute;
		
		shift.value = tnowD;
		shiftT.value = tnowT;
	}

	function openSchedule() {
		var nID = document.getElementById('nID').value;
		
		window.opener.location.href = 'page.php?page=person_schedule&nIDSelectObject=' + nID;
		window.close();
	}	
	
	function autoValidate() {
		$('Validate').onclick = function() {};
		loadXMLDoc2('autoValidate');		
	}
	
	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}	

</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="sAct" name="sAct" value="cur" />
<input type="hidden" id="nTime" name="nTime" value="0" />
<input type="hidden" id="nStep" name="nStep" value="0" />

<table class="search" style="width:100%;">
	<tr>
		<td class="header_buttons">
		<span id="head_window">Смяна за обект {$object}</span> 
		<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>
		{include file=object_tabs.tpl}
		</td>
	</tr>

	<tr class="odd">
		<td id="filter_result">
		
	{if $mobile}
		{if $cnt>6}
			<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">
		{else}
			<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">
		{/if}
	{/if}
	
	<!-- начало на работната част -->
	
		<table class="page_data">
			<tr>
				<td style="text-align: left; padding: 2px;">
				
					<div class="input-group" style="width:175px;">
						<span class="input-group-addon">
						<img src="images/glyphicons/cal.png" onClick="goTime();" style="width: 12px; height: 12px;"></span>
						<input type="text" name="sShift" id="sShift" value="" style="width: 72px; text-align: center;" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
						<input type="text" name="sShiftT" id="sShiftT" value="" style="width: 45px; text-align: center;" onkeypress="return formatTime(event);" maxlength="10" title="ЧЧ:ММ" />
						<span class="input-group-addon"><img src="images/glyphicons/clock.png" onClick="goTime();" style="width: 12px; height: 12px;"></span>
					</div>
					
				</td>
				<td style="width: 200px; text-align: right; padding: 2px;">
					Планирано:&nbsp;<input type="text" name="sDuty" id="sDuty" value="" style="width: 100px;" readonly />
				
				</td>
			</tr>

	  </table>
	

	<hr>

	<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="padding: 1px; width: 100%; height: 360px; overflow-x: auto; overflow-y: auto; !important"></div>
	
	</div>
 	<!-- край на работната част -->
	</td>
</tr>
</table>

<div id="search"  style="padding-top:10px;width:800px;">
	<table class="page_data" >
		<tr valign="top">
			<td valign="top" style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
				<button class="btn btn-xs btn-primary" onClick="dutyNext('prev');" ><img src="images/glyphicons/hand_left.png" style="width: 16px; height: 16px;" />Предишна </button>
				<button class="btn btn-xs btn-primary" onClick="dutyNext('next');" >Следваща <img src="images/glyphicons/hand_right.png" style="width: 16px; height: 16px;" /></button>
			</td>
			<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				{if $auto_schedule}
					<button type="button" class="btn btn-xs btn-success" name="Validate" onClick="autoValidate(); return false;" title="Валидирай всички смени"><img src="images/glyphicons/refresh.png" style="width: 14px; height: 14px;"> Валидация </button>
				{/if}
				<button id="butShift" class="btn btn-xs btn-success" onClick="goDuty();"><img src="images/glyphicons/change.png" style="width: 14px; height: 14px;"> Смяна </button>
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
	
	{if !$edit.object_duty_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}			
</script>