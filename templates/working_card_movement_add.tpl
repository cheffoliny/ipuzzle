{literal}
<script>
	rpc_debug = true;

	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'obj' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
			}
		}
	}
	
	function onSuggestObject(aParams) {
		$('nObject').value = aParams.KEY;
		
		rpc_on_exit = function() {
			document.getElementById( 'nIDFirm' ).value = $("nTempIDFirm").value;
			formChangeByObject();
		}
		
		loadXMLDoc2( 'getunprocessed' );
	}
	
	function formChange() {
		document.getElementById('sAct').value = 'select';
		objChange();
		loadXMLDoc2('load');
	}
	
	function formChangeByObject() {
		document.getElementById('sAct').value = 'select';
		
		rpc_on_exit = function() {
			document.getElementById( 'nIDOffice' ).value = $("nTempIDOffice").value;
			rpc_on_exit = function() {}
		}
		
		loadXMLDoc2( 'load' );
	}
	
	function objChange() {
		$('nObject').value = 0;
		$("sLastService").value = "";
		$('obj').value = '';
	}
	
	function typeChange(obj) {
		var reason = $('nIDReason');
		
		if ( obj.value != 'holdup' ) {
			reason.disabled = true;
		} else {
			reason.disabled = false;
		}
	}
	
	function formSubmit()
	{
		loadXMLDoc2('save', 3);
	}
	
	function formSubmitAndPlanning() {

		loadXMLDoc2('save', 3);
		
		rpc_on_exit = function( nCode )	{
			if( !parseInt( nCode ) ) {
				var nID = $('nID').value;
				window.open('page.php?page=tech_planning_schedule&id_request='+nID,'','height=500,width=1000');
			}
			
			rpc_on_exit = function( nCode ) {}
		}
	}
	
	function openObjectServices()
	{
		var nID = $("nObject").value;
		
		if( nID != 0 )
		{
			dialogObjectSupport( '&nID=' + nID );
		}
	}
</script>
{/literal}

<dlcalendar click_element_id="imgsAlarmD" input_element_id="sAlarmD" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard}">
		<input type="hidden" id="nObject" name="nObject" value="0" />

		<input type="hidden" id="sAct" name="sAct" value="load" />
		
		<input type="hidden" id="nNum" name="nNum" value="0" />
		<input type="hidden" id="nOld" name="nOld" value="0" />
		<input type="hidden" id="sUnprocessed" name="sUnprocessed" value="" />
		
		<input type="hidden" id="nTempIDFirm" name="nTempIDFirm" value="0" />
		<input type="hidden" id="nTempIDOffice" name="nTempIDOffice" value="0" />

		<input type="hidden" id="nToPlanning" name="nToPlanning" value="0">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} СИГНАЛ</div>

		<table class="input">
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			
			<tr class="even">
				<td>Oбект:</td>
				<td>
					<input type="text" name="obj" id="obj" style="width: 238px;" suggest="suggest" queryType="obj" queryParams="nIDOffice" onChange="objChange();" />
				</td>
			</tr>
			
			<tr class="even">
				<td>Фирма:</td>
				<td>
					<select name="nIDFirm" id="nIDFirm" style="width: 240px;" onChange="formChange();" ></select>
				</td>
			</tr>

			<tr class="even">
				<td>Регион:</td>
				<td>
					<select name="nIDOffice" id="nIDOffice" style="width: 240px;" onChange="objChange();" ></select>
				</td>
			</tr>

			<tr class="even">
				<td>Позивна:</td>
				<td>
					<select name="sPatrul" id="sPatrul" style="width: 240px;" />
				</td>
			</tr>
	
			<tr class="even">
				<td>Сигнал:</td>
				<td>
					<select name="sSignal" id="sSignal" style="width: 240px;" />
				</td>
			</tr>			
			
			<tr class="even">
				<td align="left" style="width: 105px;">Аларма:&nbsp;</td>
				<td style="width: 180px;">
					<input type="text" name="sAlarmH" id="sAlarmH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" />&nbsp;
					<input type="text" name="sAlarmD" id="sAlarmD" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="imgsAlarmD" />
				</td>
			</tr>
			
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
		</table>
		
		<table class="input">
			<tr class="odd">
				<td width="250">
					&nbsp;
				</td>
				<td style="text-align:right;">
					<button type="button" onClick="formSubmit();" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Откажи </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>