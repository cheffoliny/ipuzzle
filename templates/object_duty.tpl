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

	{include file='object_tabs.tpl'}

	<div class="container-fluid mb-4" id="filter_result">
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
		<div class="row clearfix mt-2 h-75" id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="overflow: auto;"></div>

	</div>

	<nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg" id="search">
		<div class="col-6 pl-0">
			<div class="input-group input-group-sm">
				<button class="btn btn-sm btn-primary mr-1" onClick="dutyNext('prev');" ><i class="far fa-angle-left"></i> Предишна </button>
				<button class="btn btn-sm btn-primary" onClick="dutyNext('next');" >Следваща <i class="far fa-angle-right"></i> </button>
			</div>
		</div>
		<div class="col">
			<div class="input-group input-group-sm ml-1">
				{if $auto_schedule}
					<button class="btn btn-sm btn-success mr-1" name="Validate" onClick="autoValidate(); return false;" title="Валидирай всички смени"><img src="images/glyphicons/refresh.png" style="width: 14px; height: 14px;"> Валидация </button>
				{/if}

				<button class="btn btn-sm btn-success mr-1"	onClick="goDuty();"         ><i class="fa fa-plus"></i> Смяна </button>
				<button class="btn btn-sm btn-light mr-1"	onClick="openSchedule();"       ><i class="fas fa-calendar"  ></i> График </button>
				<button class="btn btn-sm btn-danger"	    onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
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