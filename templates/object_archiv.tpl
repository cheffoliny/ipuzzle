{literal}
<script>
	rpc_debug = true;
	
	function images() {
		var alarm = $('alarm').value;
		var warn = $('warn').value;
		//alert(rest.length);
		if ( alarm.length > 0 ) {
			var obj = alarm.split('@');
			var img = '';
			for ( i = 0; i < obj.length; i++ ) {
				var sig = obj[i].split(',');
				var img = img+'&nbsp;&nbsp;<img class="ui-signal-image" src="signal_images/'+sig[0]+'.bmp" alt="" title="'+sig[2]+'\n'+sig[1]+'" />';
			}
			img = img+'&nbsp;&nbsp;';
			var span = $('images');
			span.innerHTML = img;			
		}

		if ( warn.length > 0 ) {
			var obj2 = warn.split('@');
			var img2 = '&nbsp;&nbsp;|&nbsp;&nbsp;';
			for ( i = 0; i < obj2.length; i++ ) {
				var sig2 = obj2[i].split(',');
				var img2 = img2+'&nbsp;&nbsp;<img class="ui-signal-image" src="signal_images/'+sig2[0]+'.bmp" alt="" title="'+sig2[2]+'\n'+sig2[1]+'" />';
			}
			var span2 = $('images2');
			span2.innerHTML = img2;
		}
	}
	
	function load() {
		loadXMLDoc2('result');
	}

	function formRefresh() {
		$('noTest').value = 0;
		loadXMLDoc2('result');
	}
	
	function onPrint(type) {
		$('noTest').value = 1;
		loadDirect(type);
	}	
	
	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}		
</script>
{/literal}

<dlcalendar click_element_id="sPeriodFrom" input_element_id="sPeriodFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="sPeriodTo" input_element_id="sPeriodTo" tool_tip="Изберете дата"></dlcalendar>

<form name="form1" id="form1" class="ui-object-core ui-object-archive" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="max" name="max" value="" />
    <input type="hidden" id="alarm" name="alarm" value="" />
    <input type="hidden" id="warn" name="warn" value="" />
    <input type="hidden" id="noTest" name="noTest" value="0" />
    <input type="hidden" id="num" name="num" value="{$num}" />

    {include file="object_tabs.tpl"}

    <div id="result" class="ui-object-result ui-object-archive-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

    <nav class="navbar fixed-bottom flex-row mb-0 py-0 navbar-expand-lg py-md ui-object-actions ui-object-archive-actions" id="search">
        <div class="col-4 col-sm-4 col-lg-4" title="">
            <div class="input-group input-group-sm ml-1">
                <button type="button" class="btn btn-sm btn-success ml-1" onclick="onPrint('export_to_xls');"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Excel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="onPrint('export_to_pdf');"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> PDF</button>
            </div>
        </div>
        <div id="filter_result" class="col-5 col-sm-5 col-lg-5" title="">

            <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                    <div class="input-group-prepend">
						<span class="ui-icon ui-icon-calendar" aria-hidden="true"></span>
                    </div>
                    <input class="form-control"                   type="text" name="sPeriodFromH" id="sPeriodFromH"  onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" placeholder="00:00" />
                    <input class="form-control input-group-addon pl-1" type="text" name="sPeriodFrom" id="sPeriodFrom" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );"  value="{$date_first}" />

                    <input class="form-control input-group-addon pl-1 ml-1" type="text" name="sPeriodToH" id="sPeriodToH" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" value="{$time_now}" placeholder="00:00" />
                    <input class="form-control input-group-addon pl-1" type="text" name="sPeriodTo" id="sPeriodTo" maxlength="9" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
                </div>
            </div>
        </div>
        <div class="col-3 col-sm-3 col-lg-3">
            <div class="input-group input-group-sm ml-1">
                <button type="button" class="btn btn-sm btn-success ml-1" onClick="formRefresh();"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span> Обнови </button>
                <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
            </div>
        </div>
    </nav>

</form>


<!-- край на работната част -->

<div id="NoDisplay" style="display:none"></div>


<script>
	$('noTest').value = 0;
	loadXMLDoc2('result');
	
{literal}
	rpc_on_exit = function() {
		images();
	}
{/literal}

	{if !$edit.object_archiv_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}	
</script>
