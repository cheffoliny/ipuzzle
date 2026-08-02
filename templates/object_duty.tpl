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
				setDutyButton(butt, 'Изтрий', 'ui-icon-delete');
				butt.disabled = false;
			} else if ( step > 1 ) {
				setDutyButton(butt, 'Изтрий', 'ui-icon-delete');
				butt.disabled = true;
			} else {
				setDutyButton(butt, 'Смяна', 'ui-icon-plus');
				butt.disabled = false;
			}
		}
	}	

	function setDutyButton(button, label, iconClass) {
		if (!button) return;
		button.value = label;
		if (button.tagName && button.tagName.toLowerCase() == 'button') {
			while (button.firstChild) button.removeChild(button.firstChild);
			var icon = document.createElement('span');
			icon.className = 'ui-icon ' + iconClass;
			icon.setAttribute('aria-hidden', 'true');
			button.appendChild(icon);
			button.appendChild(document.createTextNode(' ' + label));
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

<form name="form1" id="form1" class="ui-nomenclature-dialog ui-schedule-dialog ui-object-core ui-object-duty-dialog" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="sAct" name="sAct" value="cur" />
<input type="hidden" id="nTime" name="nTime" value="0" />
<input type="hidden" id="nStep" name="nStep" value="0" />

	{include file='object_tabs.tpl'}

	<div class="container-fluid ui-object-duty-content" id="filter_result">
		<table class="page_data ui-object-duty-toolbar">
			<tr>
				<td style="text-align: left; padding: 2px;">

					<div class="input-group" style="width:175px;">
						<span class="input-group-addon">
						<button type="button" class="ui-schedule-addon-button" title="Текущи дата и час" onClick="goTime();"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button></span>
						<input type="text" name="sShift" id="sShift" value="" style="width: 72px; text-align: center;" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
						<input type="text" name="sShiftT" id="sShiftT" value="" style="width: 45px; text-align: center;" onkeypress="return formatTime(event);" maxlength="10" title="ЧЧ:ММ" />
						<span class="input-group-addon"><button type="button" class="ui-schedule-addon-button" title="Текущи дата и час" onClick="goTime();"><span class="ui-icon ui-icon-clock" aria-hidden="true"></span></button></span>
					</div>

				</td>
				<td style="width: 200px; text-align: right; padding: 2px;">
					Планирано:&nbsp;<input type="text" name="sDuty" id="sDuty" value="" style="width: 100px;" readonly />
				</td>
			</tr>
		</table>
		<div class="ui-object-result ui-object-duty-result" id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

	</div>

	<nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg ui-object-actions ui-object-duty-actions" id="search">
		<div class="col-6 pl-0">
			<div class="input-group input-group-sm">
				<button type="button" class="btn btn-sm btn-primary mr-1" onClick="dutyNext('prev');"><span class="ui-icon ui-icon-left" aria-hidden="true"></span> Предишна </button>
				<button type="button" class="btn btn-sm btn-primary" onClick="dutyNext('next');">Следваща <span class="ui-icon ui-icon-right" aria-hidden="true"></span></button>
			</div>
		</div>
		<div class="col">
			<div class="input-group input-group-sm ml-1">
				{if $auto_schedule}
					<button type="button" class="btn btn-sm btn-success mr-1" id="Validate" name="Validate" onClick="autoValidate(); return false;" title="Валидирай всички смени"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span> Валидация </button>
				{/if}

				<button type="button" class="btn btn-sm btn-success mr-1" id="butShift" onClick="goDuty();"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Смяна </button>
				<button type="button" class="btn btn-sm btn-light mr-1" onClick="openSchedule();"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span> График </button>
				<button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
			</div>
		</div>
	</nav>
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
