{literal}
	<script>
		rpc_debug = true;
	
		function onClickAuto(obj) {
			
			if(obj.checked == true) {
				$('total_sum').disabled = false;
				$('total_orders').disabled = false;
				$('sRobotFromDate').disabled = false;
				$('sPeriod').disabled = false;
			} else {
				$('total_sum').disabled = true;
				$('total_orders').disabled = true;
				$('sRobotFromDate').disabled = true;
				$('sPeriod').disabled = true;
			}
		}
		
		function changePaidType(value) {
			if(value == 1) {
				$('email_send').disabled = false;
			} else {
				$('email_send').disabled = true;
				$('email_send').checked = false;
			}
		}
		
		function onInit() {
			loadXMLDoc2('load');
		}
		
		function copy_option_to( lid, rid, direction )
		{
			clear_flag = false;
			if( direction == 'right' )
			{
				sid = lid;
				id = rid;
			}
			else
			{
				sid = rid;
				id = lid;
			}
			obj = document.getElementById( sid );
			
			if( obj.options.length && obj.selectedIndex != -1 )
			{
				while( obj.selectedIndex != -1 )
				{
					OPT = obj.options[obj.selectedIndex];
					vSEL = document.getElementById(id);
					nOPT = document.createElement( 'OPTION' );
					nOPT.value = OPT.value;
					nOPT.text = OPT.text;
					
					for( i = 0; i < vSEL.options.length; i++ )
					{
						if( vSEL.options[i].text > nOPT.text && vSEL.options[i].value != '' )break;
					}
					
					if( OPT.value == '' )
					{
						i = 0;
						clear_flag = true;
					}
					
					vSEL.add( nOPT, ( isIE ) ? i : vSEL.options[i] );
					obj.remove(obj.selectedIndex);
				}
				
				if( direction == 'right' && clear_flag )
				{
					while( vSEL.options.length > 1 )
						vSEL.remove( 1 );
					
					obj.disabled = true;
				}
				
				if( direction == 'left' && clear_flag )
				{
					vSEL.disabled = false;
				}
			}
		}
		
		function onSave()
		{
			select_all_options( 'nomenclatures_current' );
			
			loadXMLDoc2( "save", 5 );
		}
		
		function onDatesChange( nPeriod )
		{
			var aIDs 		= new Array();
			var aElements 	= new Array();
			
			aIDs[0] = "sDocDateFrom";
			aIDs[1] = "sDocDateTo";
			aIDs[2] = "sLastOrderFrom";
			aIDs[3] = "sLastOrderTo";
			aIDs[4] = "sCreateDateFrom";
			aIDs[5] = "sCreateDateTo";
			aIDs[6] = "sDocDatePeriod";
			aIDs[7] = "sLastOrderPeriod";
			aIDs[8] = "sCreateDatePeriod";
			
			for( var nI = 0; nI <= 4; nI += 2 )
			{
				var nS = ( nI / 2 ) + 6;
				
				aElements[nI] 		= document.getElementById( aIDs[nI] );
				aElements[nI + 1] 	= document.getElementById( aIDs[nI + 1] );
				aElements[nS] 		= document.getElementById( aIDs[nS] );
				
				if( !aElements[nI] || !aElements[nI + 1] || !aElements[nS] ) break;
				
				switch( nPeriod )
				{
					case 0:
						if( aElements[nI].value != "" || aElements[nI + 1].value != "" ) aElements[nS].value = "";
						break;
					
					case 1:
						if( aElements[nS].value != "" ){ aElements[nI].value = ""; aElements[nI + 1].value = "" };
						break;
					
					default:
						break;
				}
			}
		}
	</script>

{/literal}

<dlcalendar click_element_id="editDocDateFrom" 		input_element_id="sDocDateFrom" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editDocDateTo" 		input_element_id="sDocDateTo" 		tool_tip="Изберете дата"></dlcalendar>

<dlcalendar click_element_id="editLastOrderFrom" 	input_element_id="sLastOrderFrom" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editLastOrderTo" 		input_element_id="sLastOrderTo" 	tool_tip="Изберете дата"></dlcalendar>

<dlcalendar click_element_id="editCreateDateFrom" 	input_element_id="sCreateDateFrom" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editCreateDateTo" 	input_element_id="sCreateDateTo" 	tool_tip="Изберете дата"></dlcalendar>

<dlcalendar click_element_id="editRobotFromDate" 	input_element_id="sRobotFromDate" 	tool_tip="Изберете дата"></dlcalendar>

<form id="form1">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<div class="page_caption">{if $nID}Редактиране{else}Създаване{/if} на филтър</div>

	<table class="input" style="margin:20px 0px 10px 0px;">
		<tr class="even">
			<td style="width:100px;">
				&nbsp;
			</td>
			<td align="right">
				Име:&nbsp;
			</td>
			<td style="width:210px;">
				<input type="text" name="sName" id="sName" style="width:200px;">
			</td>
			<td align="right">
				<input type="checkbox" class="clear" name="is_default" id="is_default">
			</td>
			<td align="left">
				Филтър по подразбиране
			</td>
		</tr>
	</table>
	<table class="input">
		<tr>
			<td>
				<fieldset>
				<legend>Документ</legend>
				<table class="input">
					<tr class="even">
						<td align="right">Номер :&nbsp;</td>
						<td align="left">
							<input type="text" id="num_from" name="num_from" class="inp100" onkeypress="return formatNumber(event);">
							&nbsp;
							до&nbsp;
							<input type="text" id="num_to" name="num_to" class="inp100" onkeypress="return formatNumber(event);">
						</td>
						<td colspan="2">
							<input type="checkbox" class="clear" name="email_send" id="email_send" disabled title="Само при плащане по банка!">&nbsp;
							Разпращане по email
						</td>
						<td align="right">
							Вид:
						</td>
						<td>
							<select name="sDocType" id="sDocType" style="width:160px;">
								<option value="0">Всички</option>
								<option value="1">квитанция</option>
								<option value="2">данъчна фактура</option>
								<option value="5">опр. квитанция</option>
								<option value="3">кредитно известие</option>
								<option value="4">дебитно известие</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Стойност :&nbsp;</td>
						<td align="left" colspan="3">
							<input type="text" name="price_from" id="price_from" class="inp100" onkeypress="return formatWeight(event);">
							&nbsp;
							до&nbsp;
							<input type="text" name="price_to" id="price_to" class="inp100" onkeypress="return formatWeight(event);">
						</td>
						<td align="right">
							Статус:
						</td>
						<td>
							<select name="sStatus" id="sStatus" style="width:160px;">
								<option value="0">Всички</option>
								<option value="1">Анулирани</option>
								<option value="2" selected>Неанулирани</option>
							</select>
						</td>
					</tr>
					<tr class="even">
						<td align="right">Дата на документа :&nbsp;</td>
						<td align="left">
							<input type="text" name="sDocDateFrom" id="sDocDateFrom" class="inp75" onkeypress="return formatDate(event, '.');" onchange="onDatesChange( 0 );" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editDocDateFrom" />
							&nbsp;
							до&nbsp;
							<input type="text" name="sDocDateTo" id="sDocDateTo" class="inp75" onkeypress="return formatDate(event, '.');" onchange="onDatesChange( 0 );" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editDocDateTo" />
						</td>
						<td align="right">Период :&nbsp;</td>
						<td align="left">
							<select name="sDocDatePeriod" id="nDocDatePeriod" class="select50" onchange="onDatesChange( 1 );">
								<option value="">---</option>
								{literal}
									<script>
										for( var i = 1; i <= 6; i++ )
										{
											document.write( "<option value=\"" + i + "\">" + i + "</option>" );
										}
									</script>
								{/literal}
							</select>&nbsp;мес.&nbsp;
						</td>
						<td align="right">
							Погасеност:
						</td>
						<td>
							<select name="sPaidStatus" id="sPaidStatus" style="width:160px;"> 
								<option value="0">Всички</option>
								<option value="1">Погасени</option>
								<option value="2">Частично погасени</option>
								<option value="3">Непогасени</option>
								<option value="4">Не или частично погасени</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Последно плащане :&nbsp;</td>
						<td align="left">
							<input type="text" name="sLastOrderFrom" id="sLastOrderFrom" class="inp75" onkeypress="return formatDate(event, '.');" onchange="onDatesChange( 0 );" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editLastOrderFrom" />
							&nbsp;
							до&nbsp;
							<input type="text" name="sLastOrderTo" id="sLastOrderTo" class="inp75" onkeypress="return formatDate(event, '.');" onchange="onDatesChange( 0 );" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editLastOrderTo" />
						</td>
						<td align="right">Период :&nbsp;</td>
						<td align="left">
							<select name="sLastOrderPeriod" id="nLastOrderPeriod" class="select50" onchange="onDatesChange( 1 );">
								<option value="">---</option>
								{literal}
									<script>
										for( var i = 1; i <= 6; i++ )
										{
											document.write( "<option value=\"" + i + "\">" + i + "</option>" );
										}
									</script>
								{/literal}
							</select>&nbsp;мес.&nbsp;
						</td>
						<td align="right">
							Плащане:
						</td>
						<td>
							<select id="paid_type" name="paid_type" onchange="changePaidType(this.value);" style="width:100px;">
								<option value="0">Всички</option>
								<option value="1">по банка</option>
								<option value="2">в брой</option>
							</select>
						</td>
					</tr>
					<tr class="even">
						<td align="right">Генериран :&nbsp;</td>
						<td align="left">
							<input type="text" name="sCreateDateFrom" id="sCreateDateFrom" class="inp75" onkeypress="return formatDate(event, '.');" onchange="onDatesChange( 0 );" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editCreateDateFrom" />
							&nbsp;
							до&nbsp;
							<input type="text" name="sCreateDateTo" id="sCreateDateTo" class="inp75" onkeypress="return formatDate(event, '.');" onchange="onDatesChange( 0 );" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editCreateDateTo" />
						</td>
						<td align="right">Период :&nbsp;</td>
						<td align="left">
							<select name="sCreateDatePeriod" id="nCreateDatePeriod" class="select50" onchange="onDatesChange( 1 );">
								<option value="">---</option>
								{literal}
									<script>
										for( var i = 1; i <= 6; i++ )
										{
											document.write( "<option value=\"" + i + "\">" + i + "</option>" );
										}
									</script>
								{/literal}
							</select>&nbsp;мес.&nbsp;
						</td>
						<td align="right">
							Плащане по Ордер:
						</td>
						<td>
							<select id="paid_type_order" name="paid_type_order" style="width: 100px;">
								<option value="0">Всички</option>
								<option value="1">по банка</option>
								<option value="2">в брой</option>
							</select>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<table class="input">
		<tr>
			<td style="width:50%">
				<fieldset>
				<legend>Служител създал</legend>
				<table class="input">
					<tr>
						<td align="right">
							Фирма&nbsp;
						</td>
						<td>
							<select id="nIDFirmCreator" name="nIDFirmCreator" onchange="loadXMLDoc2('loadCreatorOffices');" style="width:150px;"></select>
						</td>
					</tr>
					<tr>
						<td align="right">
							Регион&nbsp;
						</td>
						<td>
							<select id="nIDOfficeCreator" name="nIDOfficeCreator" style="width:150px;"></select>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td style="width:50%">
				<fieldset>
				<legend>Обекти от</legend>
				<table class="input">
					<tr>
						<td align="right">
							Фирма&nbsp;
						</td>
						<td>
							<select id="nIDFirmObjects" name="nIDFirmObjects" onchange="loadXMLDoc2('loadObjectsOffices');" style="width:150px;"></select>
						</td>
					</tr>
					<tr>
						<td align="right">
							Регион&nbsp;
						</td>
						<td>
							<select id="nIDOfficeObjects" name="nIDOfficeObjects" style="width:150px;"></select>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<table class="input" style="margin-top:10px;">
		<tr>
			<td>
				<fieldset>
				<legend>Доставчик / Клиент</legend>
				<table class="input">
					<tr>
						<td>
							Име на клиент
						</td>
						<td>
							<input type="text" id="client_name" name="client_name" class="inp150" />
						</td>
						<td>
							ЕИН на клиента:
						</td>
						<td>
							<input type="text" id="client_ein" name="client_ein" class="inp150" />
						</td>
						<td>
							ЕИК на клиента:
						</td>
						<td>
							<input type="text" id="client_eik" name="client_eik" onkeypress="return formatNumber(event);" class="inp150" />
						</td>
					</tr>
					<tr class="even">
						<td>
							Доставчик:
						</td>
						<td>
							<select id="sDeliverer" name="sDeliverer" style="width:150px;"></select>
						</td>
						<td colspan="4">
							&nbsp;
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<table class="input" style="margin-top:10px;">
		<tr>
			<td>
				<fieldset>
				<legend>Видими полета</legend>
				<table class="input">
					<tr class="even">
						<td align="right">
							<input type="checkbox" class="clear" name="show_date" id="show_date">
						</td>
						<td>
							Дата на док.
						</td>
						<td align="right">
							<input type="checkbox" class="clear" name="show_type" id="show_type">
						</td>
						<td>
							Тип на док.
						</td>
						<td align="right">
							<input type="checkbox" class="clear" name="show_deliverer" id="show_deliverer">
						</td>
						<td>
							Доставчик
						</td>
					</tr>
					<tr>
						<td align="right">
							<input type="checkbox" class="clear" name="show_client" id="show_client">
						</td>
						<td>
							Клиент
						</td>
						<td align="right">
							<input type="checkbox" class="clear" name="show_total_sum" id="show_total_sum">
						</td>
						<td>
							Сума
						</td>
						<td align="right">
							<input type="checkbox" class="clear" name="show_orders_sum" id="show_orders_sum">
						</td>
						<td>
							Погасена сума
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							<input type="checkbox" class="clear" name="show_last_order" id="show_last_order">
						</td>
						<td>
							Последен ордер
						</td>
						<td align="right">
							<input type="checkbox" class="clear" name="show_created_user" id="show_created_user">
						</td>
						<td>
							Служител генерирал
						</td>
						<td align="right">
							<input type="checkbox" class="clear" name="show_created_time" id="show_created_time">
						</td>
						<td>
							Дата на генериране
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							<input type="checkbox" class="clear" name="show_deal_office" id="show_deal_office">
						</td>
						<td>
							Място на сделката
						</td>
						<td colspan="4">&nbsp;</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td>
				<fieldset>
				<legend>Робот</legend>
				<table class="input" border="0">
					<tr class="even">
						<td>
							&nbsp;
						</td>
						<td align="left">
							<input type="checkbox" class="clear" name="is_auto" id="is_auto" onclick="onClickAuto(this);">
							Автоматичен
						</td>
						<td rowspan="3" style="width:150px;">
							<fieldset>
							<legend>Събира тотали за</legend>
							<table class="input">
								<tr class="odd">
									<td align="right">
										<input type="checkbox" class="clear" name="total_sum" id="total_sum" disabled>
									</td>
									<td>
										Сума
									</td>
								</tr>
								<tr>
									<td align="right">
										<input type="checkbox" class="clear" name="total_orders" id="total_orders" disabled>
									</td>
									<td>
										Погасена сума
									</td>
								</tr>
							</table>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td style="width:100px;">
							Дата на пускане
						</td>
						<td align="left">
							<input type="text" name="sRobotFromDate" id="sRobotFromDate" class="inp75" onkeypress="return formatDate(event, '.');" disabled/>
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editRobotFromDate"/>
						</td>
					</tr>
					<tr class="even">
						<td style="width:100px;">
							През период от
						</td>
						<td>
							<select name="sPeriod" id="sPeriod" style="width:100px;" disabled>	
								<option value="1">ден</option>
								<option value="2">седмица</option>
								<option value="3">месец</option>
							</select>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<table class="input" style="margin-top:10px;">
		<tr>
			<td style="width: 500px;">
				<fieldset style="padding-left: 5px; padding-right: 5px;">
				<legend>Номенклатури :</legend>
					<table>
						<tr>
							<td>
								<select name="nomenclatures_all" id="nomenclatures_all" style="width: 250px; height: 70px;" size="10" ondblclick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' );" multiple="multiple">
								</select>
							</td>
							<td>
								<button id=b25 name="button" title="Добави Номенклатура" style="width: 20px;" onClick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' ); return false;"><img src=images/mright.gif /></button></br>
								<button id=b25 name="button" title="Премахни Номенклатура" style="width: 20px;" onClick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'left' ); return false;"><img src=images/mleft.gif /></button>
							</td>
							<td>
								<select name="nomenclatures_current[]" id="nomenclatures_current" style="width: 250px; height: 70px;" size="10" ondblclick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'left' );" multiple="multiple">
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<td>
				&nbsp;
			</td>
			<td align="right" valign="bottom">
				<button onclick="onSave();" class="search"><img src="images/confirm.gif">Запиши</button>
				<button onclick="window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
	</table>

</form>

<script>
	onInit();
</script>