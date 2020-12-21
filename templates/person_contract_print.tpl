{literal}
	<script>
		rpc_debug=true;
		
		function printContract( id )
		{
			$("nIDContract").value = id;
			
			loadDirect( 'export_to_pdf' );
		}
		
		function confirmPrint()
		{
			var sErrors = "";
			
			$("nIDContract").value = "";
			
			//Validation
			if( $("nNum").value == "" )
			{
				sErrors += "\n Въведете Номер!";
			}
			//End Validation
			
			if( sErrors != "" )
			{
				alert( "Имате грешки при попълването: \n" + sErrors );
				return false;
			}
			
			loadDirect( 'export_to_pdf' );
		}
		
		function changeRewardOptional( id )
		{
			switch( id )
			{
				case "sRewardOptional1":
					
					if( document.getElementById( id ).value == "" )
					{
						document.getElementById( "sRewardOptional2" ).value = "";
						document.getElementById( "sRewardOptional3" ).value = "";
						document.getElementById( "sRewardOptional2Row" ).style.display = "none";
						document.getElementById( "sRewardOptional3Row" ).style.display = "none";
					}
					else
					{
						document.getElementById( "sRewardOptional2Row" ).style.display = "block";
					}
					
				break;
				
				case "sRewardOptional2":
					
					if( document.getElementById( id ).value == "" )
					{
						document.getElementById( "sRewardOptional3" ).value = "";
						document.getElementById( "sRewardOptional3Row" ).style.display = "none";
					}
					else
					{
						document.getElementById( "sRewardOptional3Row" ).style.display = "block";
					}
					
				break;
			}
		}
		
		function changeTermOptional( id )
		{
			switch( id )
			{
				case "sTermOptional1":
					
					if( document.getElementById( id ).value == "" )
					{
						document.getElementById( "sTermOptional2" ).value = "";
						document.getElementById( "sTermOptional2Row" ).style.display = "none";
					}
					else
					{
						document.getElementById( "sTermOptional2Row" ).style.display = "block";
					}
					
				break;
			}
		}
		
		function setPresetReason()
		{
			$("sReason").value = $("sPresetReason").value;
		}
	
	</script>
{/literal}

{if $nType eq 0 }
	<dlcalendar click_element_id="img_sDate" input_element_id="sDate" tool_tip="Изберете дата"></dlcalendar>
	<dlcalendar click_element_id="img_sToday" input_element_id="sToday" tool_tip="Изберете дата"></dlcalendar>
	<dlcalendar click_element_id="img_sStartDate" input_element_id="sStartDate" tool_tip="Изберете дата"></dlcalendar>
	
	<div class="content" style="width: 640px; height: 700px; overflow-y: auto;">
		<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
			<input type="hidden" name="nID" id="nID" value="{$nID}">
			<input type="hidden" name="nType" id="nType" value="{$nType}">
			<!--<input type="hidden" name="sEducation" id="sEducation" value="">-->
			<input type="hidden" name="nIDContract" id="nIDContract" value="">
			
			<div class="page_caption">Трудов Договор</div>
			
			<div id="contracts" name="contracts"></div><br />
			
			<fieldset>
				<table class="input">
					<tr class="odd">
						<td>Булстат:&nbsp;</td>
						<td>
							<input type="text" name="nBulstat" id="nBulstat" class="inp100" onKeyPress="return formatDigits(event);" maxlength="15" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Номер:&nbsp;</td>
						<td>
							<input type="text" name="nNum" id="nNum" class="inp100" onKeyPress="return formatDigits(event);" maxlength="7" value="" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Дата:</legend>
				<table class="input">
					<tr class="odd">
						<td>Дата:</td>
						<td>
							<input name="sDate" id="sDate" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sDate" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Днес:</td>
						<td>
							<input name="sToday" id="sToday" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sToday" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<table class="input">
					<tr class="odd">
						<td>
							На основание чл.&nbsp;
							<input type="text" name="sClause2" id="sClause2" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
							ал.&nbsp;
							<input type="text" name="sParagraph2" id="sParagraph2" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
							т.&nbsp;
							<input type="text" name="sLine" id="sLine" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;.&nbsp;
							Във връзка с чл.&nbsp;
							<input type="text" name="sClause" id="sClause" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
							ал.&nbsp;
							<input type="text" name="sParagraph" id="sParagraph" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;.&nbsp;
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>в т.ч. за ТСПО:</legend>
				<table class="input">
					<tr class="odd">
						<td width="30px">Год.</td>
						<td>
							<input type="text" name="nYears" id="nYears" size="1" onKeyPress="return formatDigits(event);" />&nbsp;
						</td>
						
						<td>&nbsp;</td>
						
						<td width="30px">Мес.</td>
						<td>
							<input type="text" name="nMonths" id="nMonths" size="1" onKeyPress="return formatDigits(event);" />&nbsp;
						</td>
							
						<td>&nbsp;</td>
						
						<td width="30px">Дни</td>
						<td>
							<input type="text" name="nDays" id="nDays" size="1" onKeyPress="return formatDigits(event);" />&nbsp;
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>На Длъжност:</legend>
				<table class="input">
					<tr class="odd">
						<td>Шифър:</td>
						<td>
							<input type="text" name="nCode" id="nCode" class="inp100" onKeyPress="return formatDigits(event);" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sPosition" id="sPosition" class="inp200" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Ръководител на Предприятието:</legend>
				<table class="input">
					<tr class="odd">
						<td>Име:</td>
						<td>
							<input type="text" name="sLeaderName" id="sLeaderName" class="inp200" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sLeaderPosition" id="sLeaderPosition" class="inp150" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<table class="input">
					<tr class="odd">
						<td width="420" colspan="2">
							Обект:&nbsp;
							<input type="text" id="sObject" name="sObject" style="width: 365px;" />
						</td>
					</tr>
					
					<tr class="even">
						<td width="420">
							<select name="sWorkPeriodType" id="sWorkPeriodType" style="width: 410px;">
								<option value="Със срок на изпитване">Със срок на изпитване</option>
								<option value="Неопределено време">Неопределено време</option>
								<option value="Определен срок">Определен срок</option>
								<option value="Извършване на определена работа">Извършване на опр. работа</option>
								<option value="Заместване">Заместване</option>
							</select>
						</td>
						<td>
							<input type="text" name="nTestPeriodMonths" id="nTestPeriodMonths" size="1" onKeyPress="return formatDigits(event);" />&nbsp;мес.
						</td>
					</tr>
					
					<tr class="odd">
						<td colspan="2">
							<table class="input">
								<tr class="odd" id="sTermOptional1Row">
									<td>-&nbsp;</td>
									<td>
										<input type="text" name="sTermOptional1" id="sTermOptional1" maxlength="80" size="100" onkeyup="changeTermOptional( this.id );" onblur="changeTermOptional( this.id );" />
									</td>
								</tr>
								<tr class="even" id="sTermOptional2Row" style="display: none;">
									<td>-&nbsp;</td>
									<td>
										<input type="text" name="sTermOptional2" id="sTermOptional2" maxlength="80" size="100" onkeyup="changeTermOptional( this.id );" onblur="changeTermOptional( this.id );" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
					
					<tr class="even">
						<td width="420">
							<select name="nIsFulltimeWork" id="nIsFulltimeWork" style="width: 325px;">
								<option value="1">Пълно работно време:</option>
								<option value="0">Непълно работно време:</option>
							</select>
							<span style="width: 10px;">&nbsp;</span>
							<input type="text" name="nFullDayHours" id="nFullDayHours" size="1" onKeyPress="return formatDigits(event);" />&nbsp;часа
						</td>
						<td>
							<input type="checkbox" id="nIsOnNightScheduleSet" name="nIsOnNightScheduleSet" class="clear" />&nbsp;По график с нощен труд
						</td>
					</tr>
					
					<tr class="odd">
						<td width="420">Трудово възнаграждение:</td>
						<td>
							<input type="text" name="nSalary" id="nSalary" class="inp50" onKeyPress="return formatMoney(event);" />&nbsp;лв.
						</td>
					</tr>
					
					<tr class="even">
						<td width="420">Постъпил на работа:</td>
						<td>
							<input name="sStartDate" id="sStartDate" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sStartDate" />
						</td>
					</tr>
					
					<tr class="odd">
						<td width="420">Годишен отпуск:</td>
						<td>
							<input type="text" name="nLeave" id="nLeave" class="inp50" onKeyPress="return formatMoney(event);" />&nbsp;дни
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Трудово възнаграждение:</legend>
				<table class="input">
					<tr class="odd" id="sRewardOptional1Row">
						<td>-&nbsp;</td>
						<td>
							<input type="text" name="sRewardOptional1" id="sRewardOptional1" maxlength="80" size="100" onkeyup="changeRewardOptional( this.id );" onblur="changeRewardOptional( this.id );" />
						</td>
					</tr>
					<tr class="even" id="sRewardOptional2Row" style="display: none;">
						<td>-&nbsp;</td>
						<td>
							<input type="text" name="sRewardOptional2" id="sRewardOptional2" maxlength="80" size="100" onkeyup="changeRewardOptional( this.id );" onblur="changeRewardOptional( this.id );" />
						</td>
					</tr>
					<tr class="odd" id="sRewardOptional3Row" style="display: none;">
						<td>-&nbsp;</td>
						<td>
							<input type="text" name="sRewardOptional3" id="sRewardOptional3" maxlength="80" size="100" onkeyup="changeRewardOptional( this.id );" onblur="changeRewardOptional( this.id );" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<table class="input">
				<tr class="odd">
					<td width="250">&nbsp;</td>
					<td style="text-align: right;">
						<button class="search" onclick="confirmPrint();"><img src="images/pdf.gif"/>&nbsp;Печат</button>
					</td>
				</tr>
			</table>
		
		</form>
	</div>
{/if}

{if $nType eq 1 }
	<dlcalendar click_element_id="img_sDate" input_element_id="sDate" tool_tip="Изберете дата"></dlcalendar>
	<dlcalendar click_element_id="img_sStartDate" input_element_id="sStartDate" tool_tip="Изберете дата"></dlcalendar>
	<dlcalendar click_element_id="img_sToday" input_element_id="sToday" tool_tip="Изберете дата"></dlcalendar>
	
	<div class="content" style="width: 640px; height: 700px; overflow-y: auto;">
		<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
			<input type="hidden" name="nID" id="nID" value="{$nID}">
			<input type="hidden" name="nType" id="nType" value="{$nType}">
			<input type="hidden" name="sEducation" id="sEducation" value="">
			<input type="hidden" name="nIDContract" id="nIDContract" value="">
			
			<div class="page_caption">Допълнително споразумение към Трудов Договор</div>
			
			<div id="contracts" name="contracts"></div><br />
			
			<fieldset>
				<table class="input">
					<tr class="odd">
						<td>Булстат:&nbsp;</td>
						<td>
							<input type="text" name="nBulstat" id="nBulstat" class="inp100" onKeyPress="return formatDigits(event);" maxlength="15" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Номер:&nbsp;</td>
						<td>
							<input type="text" name="nNum" id="nNum" class="inp100" onKeyPress="return formatDigits(event);" maxlength="7" value="" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Дата:</legend>
				<table class="input">
					<tr class="odd">
						<td>Днес:</td>
						<td>
							<input name="sToday" id="sToday" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sToday" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Считано от:</td>
						<td>
							<input name="sStartDate" id="sStartDate" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sStartDate" />
						</td>
					</tr>
					
					<tr class="even">
						<td>Дата:</td>
						<td>
							<input name="sDate" id="sDate" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sDate" />
						</td>
						
						<td colspan="3">&nbsp;</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>На основание:</legend>
				<table class="input">
					<tr class="odd">
						<td width="30px">Чл.</td>
						<td>
							<input type="text" name="sClause" id="sClause" size="1" onKeyPress="return formatDigits(event);" value="119" maxlength="3" />&nbsp;
						</td>
						
						<td width="30px">Ал.</td>
						<td>
							<input type="text" name="sParagraph" id="sParagraph" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
						
						<td width="30px">Т.</td>
						<td>
							<input type="text" name="sLine" id="sLine" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>От Длъжност:</legend>
				<table class="input">
					<tr class="odd">
						<td>Шифър:</td>
						<td>
							<input type="text" name="nCode" id="nCode" class="inp100" onKeyPress="return formatDigits(event);" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sPosition" id="sPosition" class="inp200" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>На Длъжност:</legend>
				<table class="input">
					<tr class="odd">
						<td>Шифър:</td>
						<td>
							<input type="text" name="nCodeTo" id="nCodeTo" class="inp100" onKeyPress="return formatDigits(event);" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sPositionTo" id="sPositionTo" class="inp200" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Ръководител на Предприятието:</legend>
				<table class="input">
					<tr class="odd">
						<td>Име:</td>
						<td>
							<input type="text" name="sLeaderName" id="sLeaderName" class="inp200" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sLeaderPosition" id="sLeaderPosition" class="inp150" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<table class="input">
					<tr class="even">
						<td>За:</td>
						<td>
							<select name="sWorkPeriodType" id="sWorkPeriodType" class="select300">
								<option value="">-- Изберете --</option>
								<option value="Със срок на изпитване">Със срок на изпитване</option>
								<option value="Неопределено време">Неопределено време</option>
								<option value="Определен срок">Определен срок</option>
								<option value="Извършване на определена работа">Извършване на определена работа</option>
								<option value="Заместване">Заместване</option>
							</select>
						</td>
						
						<td>&nbsp; - &nbsp;</td>
						
						<td>
							<input type="text" name="nWorkPeriodTime" id="nWorkPeriodTime" size="2" maxlength="4" onKeyPress="return formatDigits(event);" />&nbsp;мес.
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<table class="input">
					<tr class="even">
						<td>Работно време:&nbsp;</td>
						<td>
							<input type="text" name="nFullDayHours" id="nFullDayHours" size="1" onKeyPress="return formatDigits(event);" />&nbsp;часа
						</td>
					</tr>
					<tr class="odd">
						<td>Основно месечно трудово възнаграждение:&nbsp;</td>
						<td>
							<input type="text" name="nBasicSalary" id="nBasicSalary" class="inp50" onKeyPress="return formatMoney(event);" />&nbsp;лв.
						</td>
					</tr>
					<tr class="even">
						<td>Увеличение на трудовото възнаграждение:&nbsp;</td>
						<td>
							<input type="text" name="nSalary" id="nSalary" class="inp50" onKeyPress="return formatMoney(event);" />&nbsp;лв.
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Допълнителни възнаграждения:</legend>
				<table class="input">
					<tr class="odd">
						<td>
							<input type="text" name="sExtraReward1" id="sExtraReward1" maxlength="70" size="100" />
						</td>
					</tr>
					<tr class="even">
						<td>
							<input type="text" name="sExtraReward2" id="sExtraReward2" maxlength="70" size="100" />
						</td>
					</tr>
					<tr class="odd">
						<td>
							<input type="text" name="sExtraReward3" id="sExtraReward3" maxlength="70" size="100" />
						</td>
					</tr>
					<tr class="even">
						<td>
							<input type="text" name="sExtraReward4" id="sExtraReward4" maxlength="70" size="100" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<br />
			
			<table class="input">
				<tr class="odd">
					<td width="250">&nbsp;</td>
					<td style="text-align: right;">
						<button class="search" onclick="confirmPrint();"><img src="images/pdf.gif"/>&nbsp;Печат</button>
					</td>
				</tr>
			</table>
		
		</form>
	</div>
{/if}

{if $nType eq 2 }
	<dlcalendar click_element_id="img_sDate" input_element_id="sDate" tool_tip="Изберете дата"></dlcalendar>
	<dlcalendar click_element_id="img_sStartDate" input_element_id="sStartDate" tool_tip="Изберете дата"></dlcalendar>
	
	<div class="content" style="width: 640px; height: 700px; overflow-y: auto;">
		<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
			<input type="hidden" name="nID" id="nID" value="{$nID}">
			<input type="hidden" name="nType" id="nType" value="{$nType}">
			<input type="hidden" name="sEducation" id="sEducation" value="">
			<input type="hidden" name="nIDContract" id="nIDContract" value="">
			
			<div class="page_caption">Прекратяване на Трудово Правоотношение</div>
			
			<div id="contracts" name="contracts"></div><br />
			
			<fieldset>
				<table class="input">
					<tr class="odd">
						<td>Булстат:&nbsp;</td>
						<td>
							<input type="text" name="nBulstat" id="nBulstat" class="inp100" onKeyPress="return formatDigits(event);" maxlength="15" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Номер:&nbsp;</td>
						<td>
							<input type="text" name="nNum" id="nNum" class="inp100" onKeyPress="return formatDigits(event);" maxlength="7" value="" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Дата:</legend>
				<table class="input">
					<tr class="odd">
						<td>Дата:</td>
						<td>
							<input name="sDate" id="sDate" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sDate" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Считано от:</td>
						<td>
							<input name="sStartDate" id="sStartDate" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_sStartDate" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>На основание:</legend>
				<table class="input">
					<tr class="odd">
						<td width="30px">Чл.</td>
						<td>
							<input type="text" name="sClause" id="sClause" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
						
						<td width="30px">Ал.</td>
						<td>
							<input type="text" name="sParagraph" id="sParagraph" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
						
						<td width="30px">Т.</td>
						<td>
							<input type="text" name="sLine" id="sLine" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Във връзка с:</legend>
				<table class="input">
					<tr class="odd">
						<td width="30px">Чл.</td>
						<td>
							<input type="text" name="sClause2" id="sClause2" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
						
						<td width="30px">Ал.</td>
						<td>
							<input type="text" name="sParagraph2" id="sParagraph2" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
						
						<td width="30px">Т.</td>
						<td>
							<input type="text" name="sLine2" id="sLine2" size="1" onKeyPress="return formatDigits(event);" maxlength="3" />&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan="6">
							<input type="text" name="sInRel" id="sInRel" size="103" maxlength="90" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>На Длъжност:</legend>
				<table class="input">
					<tr class="odd">
						<td>Шифър:</td>
						<td>
							<input type="text" name="nCode" id="nCode" class="inp100" onKeyPress="return formatDigits(event);" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sPosition" id="sPosition" class="inp200" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Причини за прекратяване:</legend>
				<table class="input">
					<tr class="odd">
						<td>
							<select name="sPresetReason" id="sPresetReason" style="width: 550px;" onchange="setPresetReason();">
								<option value="">--- Изберете ---</option>
								<option value="По взаимно съгласие на страните изразено писмено">По взаимно съгласие на страните изразено писмено</option>
								<option value="По инициатива на лицето с подадено предизвестие">По инициатива на лицето с подадено предизвестие</option>
								<option value="По инициатива на работодателя в срока на изпитване">По инициатива на работодателя в срока на изпитване</option>
								<option value="По инициатива на лицето с подадено предизвестие, което няма да се отработва">По инициатива на лицето с подадено предизвестие, което няма да се отработва</option>
							</select>
						</td>
					</tr>
					<tr class="odd">
						<td>
							<input type="text" name="sReason" id="sReason" size="103" maxlength="90" />
						</td>
					</tr>
					<tr class="even">
						<td>
							<input type="text" name="sReason2" id="sReason2" size="103" maxlength="90" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>На лицето се изплащат следните обезщетения съгласно:</legend>
				<table class="input">
					<tr class="odd">
						<td>а)</td>
						<td>
							<input type="text" name="sCompA" id="sCompA" maxlength="70" size="100" />
						</td>
					</tr>
					<tr class="even">
						<td>б)</td>
						<td>
							<input type="text" name="sCompB" id="sCompB" maxlength="70" size="100" />
						</td>
					</tr>
					<tr class="odd">
						<td>в)</td>
						<td>
							<input type="text" name="sCompC" id="sCompC" maxlength="70" size="100" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Ръководител на Предприятието:</legend>
				<table class="input">
					<tr class="odd">
						<td width="110px">Име:</td>
						<td>
							<input type="text" name="sLeaderName" id="sLeaderName" class="inp200" />
						</td>
						
						<td>&nbsp;&nbsp;&nbsp;</td>
						
						<td>Длъжност:</td>
						<td>
							<input type="text" name="sLeaderPosition" id="sLeaderPosition" class="inp150" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<table class="input">
					<tr class="odd">
						<td width="110px">Ръководител ТРЗ:</td>
						<td>
							<input type="text" name="sLeaderTRZ" id="sLeaderTRZ" class="inp200" maxlength="50" />
						</td>
					</tr>
					<tr class="even">
						<td width="110px">Гл. счетоводител:</td>
						<td>
							<input type="text" name="sAccountant" id="sAccountant" class="inp200" maxlength="50" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<table class="input">
				<tr class="odd">
					<td width="250">&nbsp;</td>
					<td style="text-align: right;">
						<button class="search" onclick="confirmPrint();"><img src="images/pdf.gif"/>&nbsp;Печат</button>
					</td>
				</tr>
			</table>
		
		</form>
	</div>
{/if}

<script>
	loadXMLDoc2( 'result' );
</script>