{literal}
<script>
	rpc_debug = true;

	function isEmpty(aTextField) {
   		if ( (aTextField.value.length == 0) || (aTextField.value == null) ) {
      		return true;
   		}
   		else { 
   			return false; 
   		}
	}	
	
	function submit_form() {
		stat = document.getElementById('sStatus').value;

		if ( stat == 'closed' ) {
			if ( isEmpty(document.getElementById('sPlannedStart')) ) {
				alert('Въведете планирано време!');
				return false;
			}

			if ( isEmpty(document.getElementById('sPlannedEnd')) ) {
				alert('Въведете планирано време!');
				return false;
			}

			stH = document.getElementById('sRealStartH');
			st = document.getElementById('sRealStart');
			endH = document.getElementById('sRealEndH');
			end = document.getElementById('sRealEnd');
			if ( isEmpty(stH) ) stH.value = document.getElementById('sPlannedStartH').value;
			if ( isEmpty(st) ) st.value = document.getElementById('sPlannedStart').value;
			if ( isEmpty(endH) ) endH.value = document.getElementById('sPlannedEndH').value;
			if ( isEmpty(end) ) end.value = document.getElementById('sPlannedEnd').value;
			loadXMLDoc2('save', 3);
		} else loadXMLDoc2('save', 4);
		
		rpc_on_exit = function() {
			window.location.reload();
			
			rpc_on_exit = function() {}
		}
	}
	
	function changeTrigger() {
		document.getElementById('chng').value = 1;
	}

	function changeToCancel() {
		var act = document.getElementById('sStatus').value;
		var last = document.getElementById('lastStatus').value;
		
		if ( act == 'cancel' && last != 'cancel' ) {
			cancel();
		}
	}
	
	function changeType(type) {
		var arr = document.getElementById('nArrangeCount');
		
		if ( type.value == 'arrange' ) {
			arr.disabled = false;
			arr.value = '';
		} else {
			arr.disabled = true;
			arr.value = 0;
		}
	}
	
	function openObject() {
		var sParams = new String();
		
		sParams = 'nID=' + $('nIDObject').value;
		
		dialogObjectInfo( sParams );
	}

	function conPDF() {
		var nID = $('nIDContract').value;
		var url = 'api/api_general.php?action_script=api/api_contracts.php&api_action=export_to_pdf&rpc_version=2&id_contract='+nID;
		
		window.open(url);
	}
	
	function openRequest() {
		var nID = $('nIDRequest').value;
		
		dialogTechRequest(nID);
	}
	
	function cancel() {
		var work = $('nTimeOff').value;
		
		if ( work == 0 ) {
			if ( confirm("За връщане в непланирани Задачи или изтриване на лимитната карта натиснете 'OK' \nЗа връщане към картона лимитна карта без промяна натиснете 'Cancel'") ) {
				if ( confirm("За изтриване на лимитна карта натиснете 'OK' \nЗа връщане при непланираните Задачи натиснете 'Cancel'	")) {
					loadXMLDoc2('cancel');
				} else {
					loadXMLDoc2('cancel2');
				}
				
				rpc_on_exit = function() {
					window.location.reload();
					
					rpc_on_exit = function() {}
				}
			}
		}
	}
</script>
{/literal}

{if 2 < 1}
<dlcalendar click_element_id="imgPlannedStart" input_element_id="sPlannedStart" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgPlannedEnd" input_element_id="sPlannedEnd" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgRealStart" input_element_id="sRealStart" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgRealEnd" input_element_id="sRealEnd" tool_tip="Изберете дата"></dlcalendar>
{/if}

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
		<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
		<input type="hidden" id="nIDContract" name="nIDContract" value="0" />
		<input type="hidden" id="nIDRequest" name="nIDRequest" value="0" />
		<input type="hidden" id="chng" name="chng" value="0" />
		<input type="hidden" id="lastStatus" name="lastStatus" value="{$lock}" />
		<input type="hidden" id="nTimeOff" name="nTimeOff" value="{$work}" />

		<div class="page_caption">Информация за лимитна карта № {$nNum}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=limit_card_tabs.tpl}</td>
			</tr>
			<tr class="odd">
				<td>
				<!-- начало на работната част -->
		  		    <table class="input">
		  		    	<tr style="height: 20px;">
		  		    		<td colspan="8">&nbsp;</td>
		  		    	</tr>

		            <tr class="odd">
		               <td colspan=6 valign="top">
						<fieldset>
							<legend>Задача за лимитна карта:</legend>
							<table class="input">

{if $type eq 'contract'}
								<tr style="font-size: 0px; height: 5px;" class="odd"><td colspan="4"></td></tr>
								
								<tr class="even">
									<td align="right">№ договор:&nbsp;</td>
									<td>
										<input type="text" name="sContract" id="sContract" class="clear" onClick="conPDF();" style="width: 150px; cursor: pointer;" readonly />
									</td>
									
									<td align="right">РС:&nbsp;</td>
									<td align="right" style="width: 305px;">    
										<input type="text" name="sRS" id="sRS" class="clear" style="width: 300px;" readonly />
									</td>
								</tr>
								
								<tr style="font-size: 0px; height: 5px;" class="odd"><td colspan="4"></td></tr>
{/if}

{if $type eq 'holdup'}
								<tr class="even">
									<td align="right">№ Задача:&nbsp;</td>
									<td>
										<input type="text" name="sRequest" id="sRequest" class="clear" onClick="openRequest();" style="width: 150px; cursor: pointer;" readonly />
									</td>
									
									<td align="center" style="width: 305px;" rowspan="2">
										<textarea style="height: 50px; width: 280px;" name="reqInfo" id="reqInfo" class="clear" readonly ></textarea>
									</td>
								</tr>

								<tr class="even">
									<td align="right">Причина:&nbsp;</td>
									<td>
										<input type="text" name="sReason" id="sReason" class="clear"  style="width: 150px;" readonly />
									</td>									
								</tr>

{/if}
								
{if $type eq 'destroy' || $type eq 'arrange' || $type eq 'create'}
								<tr class="even">
									<td align="right">№ Задача:&nbsp;</td>
									<td>
										<input type="text" name="sDRequest" id="sDRequest" class="clear" onClick="openRequest();" style="width: 150px; cursor: pointer;" readonly />
									</td>
									
									<td align="center" style="width: 305px;" >
										<textarea style="height: 50px; width: 280px;" name="reqDInfo" id="reqDInfo" class="clear" readonly ></textarea>
									</td>
								</tr>

{/if}
								
								<tr style="font-size: 0px; height: 5px;" class="odd"><td colspan="4"></td></tr>
							</table>
						</fieldset>
						</td>
					</tr>

		            <tr class="odd">
		            <td colspan="6" valign="top">
					<fieldset>
						<legend>Основна информация:</legend>
						<table class="input">
													
							<tr class="even">
								<td style="width: 50px;" align="right">номер:&nbsp;</td>
								<td>
								{if $lock eq 'cancel'}
									<input type="text" name="nNum" id="nNum" class="clear" style="width: 80px; text-decoration: line-through;" readonly />
								{else}
									<input type="text" name="nNum" id="nNum" class="clear" style="width: 80px;" readonly />								
								{/if}
								</td>
								<td align="right" style="width: 50px;">дата:&nbsp;</td>
								<td>
									<input type="text" name="sDate" id="sDate" class="clear" style="width: 80px;" readonly />
								</td>
								<td align="right" style="width: 85px;" >статус:&nbsp;</td>
								<td align="right">
									<input type="text" name="sStatus" id="sStatus" class="clear" style="width: 105px;" readonly />
								</td>
								<td align="right">
									тип:&nbsp;<select name="sType" id="sType" style="width: 125px;" onChange="changeTrigger(); changeType(this);">
										<option value="create">Изграждане</option>
										<option value="destroy">Снемане</option>
										<option value="holdup">Профилактика</option>
										<option value="arrange">Аранжиране</option>
									</select>
								</td>
							</tr>
														
							<tr class="even">
								<td align="right">обект:&nbsp;</td>
								<td colspan="5">
										<input type="text" name="sObject" id="sObject" class="clear" onClick="openObject();" style="width: 400px;cursor: pointer;" readonly />
								</td>
								<td align="right" style="width: 200px;">
									отдалеченост:&nbsp;<input type="text" name="nDistance" id="nDistance" style="width: 80px;" onChange="changeTrigger();" />&nbsp;км.
								</td>
							</tr>
														
							<tr class="even">
								<td align="right">адрес:&nbsp;</td>
								<td colspan="6">
									<input type="text" name="sAddress" id="sAddress" class="clear" style="width: 610px;" readonly />
								</td>
							</tr>
														
							<tr class="even" nowrap>
								<td align="right">МОЛ:&nbsp;</td>
								<td colspan="6">
									<span style="vertical-align: text-bottom;"><input type="text" name="sMol" id="sMol" class="clear" style="width: 350px;" readonly /></span>
									&nbsp;&nbsp;&nbsp;&nbsp;<span style="vertical-align: top;">Телефон:&nbsp;</span>
									<input type="text" name="sPhone" id="sPhone" class="clear" style="width: 100px;" readonly />
								</td>
							</tr>
							
						</table>
						</fieldset>
					</td></tr>
					
					<tr style="height: 5px;" class="odd"><td colspan="7"></td></tr>
					
		            <tr class="odd">
		               <td colspan=6 valign="top">
						<fieldset>
							<legend>Планиране/изпълнение:</legend>
							<table class="input">
								
								<tr class="even">
									<td align="right" style="width: 100px;">планиран старт:&nbsp;</td>
									<td style="width: 180px;">
										<input type="text" name="sPlannedStartH" id="sPlannedStartH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" onChange="changeTrigger();" readonly />&nbsp;
										<input type="text" name="sPlannedStart" id="sPlannedStart" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" onChange="changeTrigger();" readonly />&nbsp;
										<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgPlannedStart" />
									</td>
									<td align="right" style="width: 100px;">планиран край:&nbsp;</td>
									<td>
										<input type="text" name="sPlannedEndH" id="sPlannedEndH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" onChange="changeTrigger();" readonly />&nbsp;									
										<input type="text" name="sPlannedEnd" id="sPlannedEnd" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" onChange="changeTrigger();" readonly />&nbsp;
										<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgPlannedEnd" />
									</td>
									<td align="right">
										<input type="text" name="nArrangeCount" id="nArrangeCount" style="width: 50px; text-align: right;" onkeypress="return formatDigits(event);" maxlength="2" onChange="changeTrigger();" readonly />&nbsp;									
									</td>
								</tr>
								
								<tr class="even">
									<td align="right">реален старт:&nbsp;</td>
									<td>
										<input type="text" name="sRealStartH" id="sRealStartH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" onChange="changeTrigger();" readonly />&nbsp;																											
										<input type="text" name="sRealStart" id="sRealStart" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" onChange="changeTrigger();" readonly />&nbsp;
										<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgRealStart" />
									</td>
									<td align="right">реален край:&nbsp;</td>
									<td>
										<input type="text" name="sRealEndH" id="sRealEndH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" onChange="changeTrigger();" readonly />&nbsp;																		
										<input type="text" name="sRealEnd" id="sRealEnd" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" onChange="changeTrigger();" readonly />&nbsp;
										<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgRealEnd"  />
									</td>
									<td align="right">бр. аранжировка&nbsp;</td>
								</tr>
								
								<tr style="height: 5px;" class="odd"><td colspan="7"></td></tr>
							</table>
						</fieldset>
						</td>
					</tr>
					
					<tr style="height: 5px;" class="odd"><td colspan="6"></td></tr>

		            <tr class="odd">
		               <td colspan=6 valign="top">
						<fieldset>
							<legend>Допълнителна информация:</legend>
							<table class="input">
							
								<tr class="odd">
									<td align="center">
										<textarea style="height: 40px; width: 660px;" id="note" name="note" onChange="changeTrigger();" ></textarea>
									</td>
								</tr>
								
								<tr style="height: 5px;" class="odd"><td colspan="6"></td></tr>
							</table>
						</fieldset>
						</td>
					</tr>

					</table>
					
					<table  class="input">
						<tr valign="top" class="odd">
						{if $work && $lock eq 'active'}
							<td valign="top" align="left" width="100px">
								<button class="search" style="background: #F09E93;" onclick="return cancel();" title="Анулиране на лимитна карта" disabled><img src="images/cancel.gif"/>Анулирай</button>&nbsp;
							</td>
						{/if}

						{if $lock eq 'cancel'}
							<td valign="top" align="left" width="100px">
								<button class="search" style="background: #F09E93;" onclick="return cancel();" title="{$person}" disabled><img src="images/cancel.gif"/>Анулирана</button>&nbsp;
							</td>
						{/if}

						{if not $work && $lock eq 'active'}
							<td valign="top" align="left" width="100px">
								<button class="search" style="background: #F09E93;" onclick="return cancel();" title="Анулиране на лимитна карта"><img src="images/cancel.gif"/>Анулирай</button>&nbsp;
							</td>
						{/if}
												
							<td valign="top" align="right" width="600px">
								<button class="search" onclick="return submit_form();" on><img src="images/confirm.gif"/>Потвърди</button>&nbsp;
							</td>
						
							<td valign="top" align="right" width="100px">
								<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
							</td>
							
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>

{literal}
<script>
	loadXMLDoc2('result');
	
	rpc_on_exit = function() {
		document.getElementById('chng').value = 0;
		var arr = document.getElementById('nArrangeCount');
		var type = document.getElementById('sType');
		
		if ( type.value == 'arrange' ) {
			arr.disabled = false;
		} else {
			arr.disabled = true;
			arr.value = 0;
		}
	}				
</script>
{/literal}

{if $lock eq 'closed' || $lock eq 'cancel'}
	{literal}
	<script>
		if( form = document.getElementById('form1') ) {
			for( i = 0; i < form.elements.length - 1; i++ ) form.elements[i].setAttribute('disabled', 'disabled');
		}
	</script>
	{/literal}
{/if}
