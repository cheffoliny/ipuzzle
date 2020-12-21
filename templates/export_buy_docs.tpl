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
			return false;
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
	
	<style>
		.separator {
			width:10px;
		}
		
		.w120 {
			width:120px;
		}
		
	</style>
{/literal}

<dlcalendar click_element_id="imgPeriodFrom" input_element_id="sPeriodFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgPeriodTo" input_element_id="sPeriodTo" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="sFile" id="sFile" value="">

    <table class="page_data" id="capt" name="capt">
        <tr>
            <td class="page_name">Експорт на Документи за покупка [разход]</td>
        </tr>
        <tr>
            <td style="color: #fff;" id="filter">{include file=finance_instruments_tabs.tpl}</td>
        </tr>
    </table>

    <table class="search" style="margin-top: 4px;">
        <tr>
            <td>
                <div class="input-group" >
                    <span class="input-group-addon">
					    <img id="imgPeriodFrom" src="images/glyphicons/forw_right.png" style="width: 10px; height: 12px; cursor:pointer;" title="Начало на периода" /></span>
					<input type="text" name="sPeriodFromH" id="sPeriodFromH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" />
					<input type="text" name="sPeriodFrom" id="sPeriodFrom" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date_first}" />
                    &nbsp;
					<input type="text" name="sPeriodToH" id="sPeriodToH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" value="{$time_now}" />&nbsp;
					<input type="text" name="sPeriodTo" id="sPeriodTo" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date_now}" />&nbsp;
					<span class="input-group-addon">
						<img id="imgPeriodTo" src="images/glyphicons/forw_left.png" style="width: 10px; height: 12px; cursor:pointer;" /></span>
                    &nbsp;
					<select name="nIDFirm" id="nIDFirm" onChange="formChange();" style="width: 200px;" ></select>
                    &nbsp;
					<select name="nIDOffice" id="nIDOffice" style="width: 200px;" ></select>
                    &nbsp;
                    <button class="btn btn-xs btn-primary" onClick="formSubmit();" title="Приложи шаблона"><img src="images/glyphicons/doc_min.png" /> Експорт </button>
				</div>
			</td>
		</tr>
	</table>
	
	<hr>
	<div id="result" rpc_excel_panel="on" rpc_paging="on" rpc_resize="on" style="overflow: auto;" ></div>

</form>

{literal}
	<script>
		//resize();
		
		onInit();
	</script>
{/literal}