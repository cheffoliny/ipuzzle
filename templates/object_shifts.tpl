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

	{include file='object_tabs.tpl'}

	<div class="container-fluid mb-4" id="filter_result">

		<div class="row clearfix mt-2 h-75" id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="overflow: auto;"></div>

	</div>
	<nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg" id="search">
		<div class="col-6 pl-0">
			<div class="input-group input-group-sm">

			</div>
		</div>
		<div class="col">
			<div class="input-group input-group-sm ml-1">
				<button class="btn btn-sm btn-success mr-1"	onClick="editShifts(0);"         ><i class="fa fa-plus"></i> Смяна </button>
				<button class="btn btn-sm btn-light mr-1"	onClick="openSchedule();"       ><i class="fas fa-calendar"  ></i> График </button>
				<button class="btn btn-sm btn-danger"	    onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
			</div>
		</div>
	</nav>

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
