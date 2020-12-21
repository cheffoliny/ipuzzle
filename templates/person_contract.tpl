{literal}
	<script>
		rpc_debug = true;
		
		function submit_form() {
			loadXMLDoc('save', 1);
			
//			var fixs = document.getElementById('fix_cost');
//			var mins = document.getElementById('min_cost')
//			type = document.getElementById('type_salary').value;
			
//			if ( type == 'fix' && parseInt(fixs.value) == 0 ) {
//				if( confirm('Не е въведена фиксирана заплата! Ще продължите ли?') ) {
//					loadXMLDoc('save', 1);
//				}
//			} else if ( type == 'min' && parseInt(mins.value) == 0 ) {
//				if( confirm('Не е въведена минимална заплата! Ще продължите ли?') ) {
//					loadXMLDoc('save', 1);
//				}
//			} else if ( ( type == 'min' && parseInt(mins.value) != 0 ) || ( type == 'fix' && parseInt(fixs.value) != 0 ) ) {
//				loadXMLDoc('save', 1);
//			}
		}		
		
		function tsalary(id) {
			var check 	= id.checked;
			var name 	= id.name;
			
			if ( name == 'fix_salary' ) {
				var obj = $('fix_cost');
				
				if ( check == false ) {
					obj.value 		= '0.00';
					obj.disabled 	= true;
				} else {
					obj.disabled 	= false;
				}
			} 
			
			if ( name == 'min_salary' ) {
				var obj = $('min_cost');
				
				if ( check == false ) {
					obj.value 		= '0.00';
					obj.disabled 	= true;
				} else {
					obj.disabled 	= false;
				}
			}	
			
			document.getElementById('type_salary').value = name;		
		}
	
	</script>
{/literal}


<dlcalendar click_element_id="img_trial_from" input_element_id="trial_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_trial_to" input_element_id="trial_to" tool_tip="Изберете дата"></dlcalendar>

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="id" name="id" value="{$id|default:0}" />
		<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
		<input type="hidden" id="type_salary" name="type_salary" value="" />

		<div class="page_caption">Трудов договор на {$person_name}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=person_tabs.tpl}</td>
			</tr>
			<tr class="odd">
				<td>
				<!-- начало на работната част -->
		  		    <table class="input" cellpadding="0" cellspacing="0" border="0">
		  		    	<tr style="height: 20px;">
		  		    		<td colspan="6">&nbsp;</td>
		  		    	</tr>

		                <tr class="even">
		                    <td style="width: 140px; white-space: nowrap;">
		                    	<input type="checkbox" id="fix_salary" name="fix_salary" style="width: 25px;" class="clear" onclick="tsalary(this);" checked /> &nbsp;Фиксирана заплата
		                    </td>
		                    <td align="left" style="width: 105px;">
		                    	<input type="text" name="fix_cost" id="fix_cost" style="width: 55px; text-align: right;" onKeyPress="return formatMoney(event);" />&nbsp;лв.
		                    	&nbsp;&nbsp;<img src="images/info.gif" style="cursor: pointer;" id="sFixSalary">
		                    </td>
		                    <td align="right">Мин. осигурителен праг&nbsp;</td>
		                    <td>
		                    	<input type="text" name="insurance" id="insurance" style="width: 55px; text-align: right;" onKeyPress="return formatMoney(event);" />&nbsp;лв.
		                    	&nbsp;&nbsp;<img src="images/info.gif" style="cursor: pointer;" id="sInsurance">
		                    </td>
		                    <td align="right">Фактор за техници&nbsp;</td>
		                    <td>
		                    	<input type="text" name="factor" id="factor" style="width: 55px; text-align: right;" onKeyPress="return formatMoney(event);" />
		                    	&nbsp;&nbsp;<img src="images/info.gif" style="cursor: pointer;" id="sFactor">
		                    </td>
		                </tr>

		                <tr class="odd">
		                    <td>
		                    	<input type="checkbox" id="min_salary" name="min_salary" style="width: 25px;" class="clear" onclick="tsalary(this);" /> &nbsp;Основна по ТД
		                    </td>
		                    <td>
		                    	<input name="min_cost" type="text" id="min_cost" style="width: 55px; text-align: right;" onKeyPress="return formatMoney(event);" />&nbsp;лв.
		                    	&nbsp;&nbsp;<img src="images/info.gif" style="cursor: pointer;" id="sMinSalary">
		                    </td>
		                    <td align="right">Прослужено време&nbsp;</td>
		                    <td>
								<input type="text" style="width: 200px;" name="serve" id="serve" class="clear" readonly />
		                    </td>
		                    <td align="right">Фактор за смени&nbsp;</td>
		                    <td>
		                    	<input type="text" name="shifts_factor" id="shifts_factor" style="width: 55px; text-align: right;" onKeyPress="return formatMoney(event);" />
		                    	&nbsp;&nbsp;<img src="images/info.gif" style="cursor: pointer;" id="sShiftsFactor">
		                    </td>

		  		    	<tr style="height: 15px;">
		  		    		<td colspan="6">&nbsp;</td>
		  		    	</tr>
		                    
		                 </tr>
		                 <tr class="even">
		                    <td colspan="6" valign="top">
								<fieldset>
									<legend>Пробен период</legend>
									<table class="input">
										<tr>
						                    <td align="right">от дата</td>
						                    <td align="left">
												<input name="trial_from" id="trial_from" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
												&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_trial_from" />
						                    </td>
											<td align="right">до дата</td>
											<td align="left">
												<input name="trial_to" id="trial_to" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
												&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_trial_to" />											
											</td>
											<td align="right">процент базово възнаграждаване</td>
											<td align="left">
												<input name="rate_reward" id="rate_reward" type="text" class="inp50" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;%
											</td>
										<tr>
									</table>
								</fieldset>
		                    </td>
		                 </tr>
		                 <tr>
		                 	<td colspan="6" valign="top">
		                 		<table border="0" class="input">
		                 			<tr>
					                 	<td>&nbsp;</td>
					                 	
		                 				<td>Образование:&nbsp;</td>
					                 	<td>
					                 		<input type="text" name="sEducation" id="sEducation" class="inp200">
					                 	</td>
					                 	
					                 	<td>Специалност:&nbsp;</td>
					                 	<td>
					                 		<input type="text" name="sSpeciality" id="sSpeciality" class="inp200">
					                 	</td>
		                 			</tr>
		                 			<tr>
					                 	<td>&nbsp;</td>
					                 	
		                 				<td>Друга специалност:&nbsp;</td>
					                 	<td>
					                 		<input type="text" name="sSpecialityOther" id="sSpecialityOther" class="inp200">
					                 	</td>
					                 	
					                 	<td>Трудов стаж:&nbsp;</td>
					                 	<td>
					                 		<input type="text" name="nLOSYears" id="nLOSYears" size="1" onKeyPress="return formatDigits(event);">
					                 		&nbsp;г.&nbsp;
					                 		<input type="text" name="nLOSMonths" id="nLOSMonths" size="1" onKeyPress="return formatDigits(event);">
					                 		&nbsp;м.&nbsp;
					                 		<input type="text" name="nLOSDays" id="nLOSDays" size="1" onKeyPress="return formatDigits(event);">
					                 		&nbsp;д.&nbsp;
					                 	</td>
		                 			</tr>
					                 <tr>
					                 	<td>&nbsp;</td>
					                 	<td>Клас :&nbsp;</td>
					                 	<td colspan="3">
					                 		<input type="text" name="nClass" id="nClass" size="2" onKeyPress="return formatMoney( event );">&nbsp;%
					                 	</td>
					                 </tr>
		                 		</table>
		                 	</td>
		                 </tr>
					</table>
					<div style="width: 785px; height: 230px; "></div>
					<table  class="input">
						<tr valign="top" class="odd">
							<td valign="top" align="right" width="800px">
								<button class="search" onclick="return submit_form();"><img src="images/confirm.gif"/>Потвърди</button>&nbsp;
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


<script>loadXMLDoc('result');//loadMainData();</script>
	{if !$personnel_edit}
		
		<script>
		if( form=document.getElementById('form1') )  
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		</script>
	{/if}