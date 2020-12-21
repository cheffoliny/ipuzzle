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
				var img = img+'&nbsp;&nbsp;<img src="signal_images/'+sig[0]+'.bmp" title="'+sig[2]+'\n'+sig[1]+'" style="width: 16px; height: 16px;" />';
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
				var img2 = img2+'&nbsp;&nbsp;<img src="signal_images/'+sig2[0]+'.bmp" title="'+sig2[2]+'\n'+sig2[1]+'" style="width: 16px; height: 16px;" />';
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

<form name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="max" name="max" value="" />
    <input type="hidden" id="alarm" name="alarm" value="" />
    <input type="hidden" id="warn" name="warn" value="" />
    <input type="hidden" id="noTest" name="noTest" value="0" />
    <input type="hidden" id="num" name="num" value="{$num}" />

    {include file=object_tabs.tpl}

    <div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

    {*{include file=object_tabs_sot.tpl}*}

    {*<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>*}



	{*<hr>*}
{*{if $mobile}*}
	{*{if $cnt>6}*}
		{*<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">*}
	{*{else}*}
		{*<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">*}
	{*{/if}*}
{*{/if}*}
    <nav class="navbar fixed-bottom flex-row mb-0 py-0 navbar-expand-lg py-md" id="search">
        <div class="col-4 col-sm-4 col-lg-4" title="">
            <div class="input-group input-group-sm ml-1">
                <button class="btn btn-sm btn-success ml-1"  onclick="onPrint('export_to_xls');"><i class="fa fa-file-excel"></i> &nbsp; Excel &nbsp;&nbsp;&nbsp; </button>
                <button class="btn btn-sm btn-danger"   onclick="onPrint('export_to_pdf');"><i class="fa fa-file-pdf"></i> &nbsp; PDF &nbsp;&nbsp;&nbsp;&nbsp; </button>
            </div>
        </div>
        <div id="filter_result" class="col-5 col-sm-5 col-lg-5" title="">

            <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                    <div class="input-group-prepend">
						<span class="fas fa-calendar-alt fa-fw" data-fa-transform="right-22 down-10"></span>
                    </div>
                    <input class="form-control"                   type="text" name="sPeriodFromH" id="sPeriodFromH"  onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" placeholder="00:00" />
                    <input class="form-control input-group-addon pl-1" type="text" name="sPeriodFrom" id="sPeriodFrom" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );"  value="{$date_first}" />

                    <input class="form-control input-group-addon pl-1 ml-1" type="text" name="sPeriodToH" id="sPeriodToH" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" value="{$time_now}" placeholder="00:00" />
                    <input class="form-control input-group-addon pl-1" type="text" name="sPeriodTo" id="sPeriodTo" maxlength="9" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
                </div>
            </div>
            {*<div class="input-group" style="width: 138px;">*}
            {*<span class="input-group-addon-warning">*}
            {*<img src="images/glyphicons/alarm.png" style="width: 12px; height: 12px; cursor:pointer;" title="Оповестени сигнали" /></span>*}
            {*<input type="text" name="announce" id="announce" style="width: 40px; text-align: right;" title="Оповестени сигнали" readonly />*}
            {*<span class="input-group-addon-ok">*}
            {*<img src="images/glyphicons/car.png" style="width: 12px; height: 12px; cursor:pointer;" title="Реално посетени" /></span>*}
            {*<input type="text" name="visited" id="visited" style="width: 40px; text-align: right;" title="Реално посетени" readonly />*}
            {*</div>*}

            {*<div class="input-group">*}
            {*<span class="input-group-addon">*}
            {*<img src="images/glyphicons/car.png" style="width: 12px; height: 12px; cursor:pointer;" title="Само с реакция" /></span>*}
            {*<input type="checkbox" id="nReact" name="nReact" class="clear" onClick="load();" {if $visited}checked{/if} />*}
            {*</div>*}

        </div>
        <div class="col-3 col-sm-3 col-lg-3">
            <div class="input-group input-group-sm ml-1">
                <button class="btn btn-sm btn-success ml-1"  onClick="formRefresh();"><i class="fas fa-sync-alt"></i> Обнови </button>
                <button class="btn btn-sm btn-danger"   onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
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
