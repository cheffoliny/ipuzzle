{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function formChange() {
			loadXMLDoc2('getOffices');
		}
				
		function formSubmit() {
			loadXMLDoc2('export', 1);
		}
				
		function checkAll( bChecked ) {
			var aCheckboxes = document.getElementsByTagName('input');
			
			for( var i=0;i<aCheckboxes.length;i++) {
				if( aCheckboxes[i].type.toLowerCase() == 'checkbox' )
					aCheckboxes[i].checked = bChecked;
			}
		}
		
		function just_do_it() {
			switch( $('sel').value ) {
				case 'check':
					checkAll( true );
					break;
				case 'uncheck':
					checkAll( false );
					break;
				case 'makeRequests':
					makeRequests();
					break;
			}
		}
		
		function delFile(id) {
			if ( confirm('Наистина ли желаете да премахнете файла?') ) {
				$('sFile').value = id;
				loadXMLDoc2('delete', 1);
			}
			
		}
		
		function viewFile(id) {
			var url = 'engine/view_export_doc.php?id='+encodeURI(id);
	
			window.open(url, "win", "width=350, height=150"); 
		}
		

		
//		function resize() {
//			var div = document.getElementById('result');
//			div.style.height = document.body.offsetHeight-140;
//		}
	</script>
	
{/literal}

<dlcalendar click_element_id="imgPeriodFrom" input_element_id="sPeriodFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgPeriodTo" input_element_id="sPeriodTo" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" class="ui-export-docs ui-finance-export-docs" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="sFile" id="sFile" value="">

    <table class="page_data ui-legacy-report-heading" id="capt" name="capt">
        <tr>
            <td class="page_name">Експорт на Документи за продажба [приход]</td>
        </tr>
        <tr>
            <td style="color: #fff;">{include file="finance_instruments_tabs.tpl"}</td>
		</tr>
	</table>

    <table class="search table-secondary ui-legacy-report-filter">
		<tr>
			<td>

                <div class="input-group input-group-sm ui-export-docs-filter">
                    <button type="button" id="imgPeriodFrom" class="ui-finance-date-trigger" title="Начало на периода"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
					<input type="text" name="sPeriodFromH" id="sPeriodFromH" class="form-control ui-export-time" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" />
					<input type="text" name="sPeriodFrom" id="sPeriodFrom" class="form-control inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date_first}" />
                    &nbsp;
					<input type="text" name="sPeriodToH" id="sPeriodToH" class="form-control ui-export-time" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" value="{$time_now}" />
					<input type="text" name="sPeriodTo" id="sPeriodTo" class="form-control inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date_now}" />
					<button type="button" id="imgPeriodTo" class="ui-finance-date-trigger" title="Край на периода"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
                    &nbsp;
				    <select name="nIDFirm" id="nIDFirm" onChange="formChange();" class="form-control select150" ></select>
                    &nbsp;
				    <select name="nIDOffice" id="nIDOffice" class="form-control select150" ></select>
                    &nbsp;
                    <button type="button" class="btn btn-sm btn-primary" onClick="formSubmit(); return false;" title="Приложи шаблона"><span class="ui-icon ui-icon-file-export" aria-hidden="true"></span> Експорт </button>
                </div>
			</td>
		</tr>
	</table>
	
	<hr>
	<div id="result" class="ui-finance-export-result" rpc_excel_panel="on" rpc_paging="on" rpc_resize="on"></div>

</form>

{literal}
	<script>
		//resize();
		
		onInit();
	</script>
{/literal}
