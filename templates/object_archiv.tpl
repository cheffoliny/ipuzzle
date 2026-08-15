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
		if ( !validateArchivePeriod() ) {
			return false;
		}
		$('noTest').value = 0;
		loadXMLDoc2('result');
		return true;
	}
	
	function onPrint(type) {
		if ( !validateArchivePeriod() ) {
			return false;
		}
		$('noTest').value = 1;
		loadDirect(type);
		return true;
	}	

	function normalizeArchiveTimeInput(input) {
		input = typeof input == 'string' ? $(input) : input;
		if ( !input ) {
			return false;
		}

		var value = input.value.replace(/\s+/g, '');
		var match = value.match(/^(\d{1,2})(?::(\d{1,2}))?$/);
		var error = 'Използвайте 24-часов формат ЧЧ:ММ (00:00–23:59).';

		if ( !match ) {
			input.setCustomValidity(error);
			return false;
		}

		var hours = parseInt(match[1], 10);
		var minutes = match[2] === undefined ? 0 : parseInt(match[2], 10);
		if ( hours > 23 || minutes > 59 ) {
			input.setCustomValidity(error);
			return false;
		}

		input.value = (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes;
		input.setCustomValidity('');
		return true;
	}

	function initArchiveTimeOptions() {
		var list = $('objectArchiveTimeOptions');
		if ( !list || list.options.length ) {
			return;
		}

		for ( var totalMinutes = 0; totalMinutes < 24 * 60; totalMinutes += 30 ) {
			var hours = Math.floor(totalMinutes / 60);
			var minutes = totalMinutes % 60;
			var option = document.createElement('option');
			option.value = (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes;
			list.appendChild(option);
		}
	}

	function archiveDateTimeValue(dateId, timeId) {
		var dateInput = $(dateId);
		var timeInput = $(timeId);
		if ( !normalizeArchiveTimeInput(timeInput) ) {
			return null;
		}
		var dateMatch = dateInput.value.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
		var timeMatch = timeInput.value.match(/^([01]\d|2[0-3]):([0-5]\d)$/);

		if ( !dateMatch || !timeMatch ) {
			return null;
		}

		var value = new Date(
			parseInt(dateMatch[3], 10),
			parseInt(dateMatch[2], 10) - 1,
			parseInt(dateMatch[1], 10),
			parseInt(timeMatch[1], 10),
			parseInt(timeMatch[2], 10),
			0,
			0
		);

		if ( value.getFullYear() != parseInt(dateMatch[3], 10) ||
			 value.getMonth() != parseInt(dateMatch[2], 10) - 1 ||
			 value.getDate() != parseInt(dateMatch[1], 10) ) {
			return null;
		}

		return value;
	}

	function validateArchivePeriod() {
		var from = archiveDateTimeValue('sPeriodFrom', 'sPeriodFromH');
		var to = archiveDateTimeValue('sPeriodTo', 'sPeriodToH');

		if ( !from ) {
			alert('Моля, въведете валидни начална дата и час.');
			$('sPeriodFrom').focus();
			return false;
		}

		if ( !to ) {
			alert('Моля, въведете валидни крайна дата и час.');
			$('sPeriodTo').focus();
			return false;
		}

		if ( from.getTime() > to.getTime() ) {
			alert('Началото на периода трябва да бъде преди края му.');
			$('sPeriodFrom').focus();
			return false;
		}

		return true;
	}
	
	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}		
</script>
{/literal}

<dlcalendar click_element_id="objectArchiveFromDate" input_element_id="sPeriodFrom" tool_tip="Изберете начална дата"></dlcalendar>
<dlcalendar click_element_id="objectArchiveToDate" input_element_id="sPeriodTo" tool_tip="Изберете крайна дата"></dlcalendar>

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
        <div class="col-3 col-sm-3 col-lg-3" title="">
            <div class="input-group input-group-sm ml-1">
                <button type="button" class="btn btn-sm btn-success ml-1" onclick="onPrint('export_to_xls');"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Excel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="onPrint('export_to_pdf');"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> PDF</button>
            </div>
        </div>
        <div id="filter_result" class="col-6 col-sm-6 col-lg-6" title="Период на архива">
            <div class="ui-object-archive-period-range" role="group" aria-label="Период на архива">
                <div class="ui-object-archive-period-endpoint">
                    <span class="ui-object-archive-period-label">ОТ</span>
                    <div class="ui-object-archive-datetime">
                        <button type="button" class="ui-object-archive-calendar-trigger" id="objectArchiveFromDate" title="Изберете начална дата" aria-label="Изберете начална дата">
                            <span class="ui-icon ui-icon-calendar" aria-hidden="true"></span>
                        </button>
                        <input class="form-control ui-object-archive-date" type="text" name="sPeriodFrom" id="sPeriodFrom" maxlength="10" placeholder="__.__.____" onkeypress="return formatDate(event, '.');" value="{$date_first}" aria-label="Начална дата" />
                        <input class="form-control ui-object-archive-time" type="text" inputmode="numeric" name="sPeriodFromH" id="sPeriodFromH" value="00:00" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]" placeholder="ЧЧ:ММ" list="objectArchiveTimeOptions" autocomplete="off" onkeypress="return formatTime(event);" onblur="normalizeArchiveTimeInput(this);" title="Начален час, 24-часов формат ЧЧ:ММ" aria-label="Начален час, 24-часов формат" />
                    </div>
                </div>

                <span class="ui-object-archive-period-separator" aria-hidden="true"><span class="ui-icon ui-icon-right"></span></span>

                <div class="ui-object-archive-period-endpoint">
                    <span class="ui-object-archive-period-label">ДО</span>
                    <div class="ui-object-archive-datetime">
                        <button type="button" class="ui-object-archive-calendar-trigger" id="objectArchiveToDate" title="Изберете крайна дата" aria-label="Изберете крайна дата">
                            <span class="ui-icon ui-icon-calendar" aria-hidden="true"></span>
                        </button>
                        <input class="form-control ui-object-archive-date" type="text" name="sPeriodTo" id="sPeriodTo" maxlength="10" placeholder="__.__.____" onkeypress="return formatDate(event, '.');" value="{$date_now}" aria-label="Крайна дата" />
                        <input class="form-control ui-object-archive-time" type="text" inputmode="numeric" name="sPeriodToH" id="sPeriodToH" value="{$time_now}" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]" placeholder="ЧЧ:ММ" list="objectArchiveTimeOptions" autocomplete="off" onkeypress="return formatTime(event);" onblur="normalizeArchiveTimeInput(this);" title="Краен час, 24-часов формат ЧЧ:ММ" aria-label="Краен час, 24-часов формат" />
                    </div>
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

    <datalist id="objectArchiveTimeOptions"></datalist>

</form>


<!-- край на работната част -->

<div id="NoDisplay" style="display:none"></div>


<script>
	$('noTest').value = 0;
	initArchiveTimeOptions();
	normalizeArchiveTimeInput('sPeriodFromH');
	normalizeArchiveTimeInput('sPeriodToH');
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
