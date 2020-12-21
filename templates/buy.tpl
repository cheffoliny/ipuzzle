{literal}

	<script>
		rpc_debug = true;
		
		var child_window;
		
		function onInit() {
			
			attachEventListener( $('nObjectNum'),  "keypress", onKeyPressObjectNum);
			attachEventListener( $('sObjectName'), "keypress", onKeyPressObjectName);
			attachEventListener( $('nPersonCode'), "keypress", onKeyPressPersonCode);
			attachEventListener( $('sPersonName'), "keypress", onKeyPressPersonName);
			
			InitSuggestForm = function() {
				for(var i=0; i<suggest_elements.length; i++) {
					switch( suggest_elements[i]['id'] ) {
						case 'nObjectNum':
						case 'sObjectName':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
						case 'nPersonCode':
						case 'sPersonName':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
						break;
					}
				}
			}
			
			loadXMLDoc2('load');
		}
		
		function onSuggestObject( aParams ) {
			var aParts = aParams.KEY.split(';');
			
			$('nIDObjectSalary').value = 	aParts[0];
			$('nObjectNum').value = 		aParts[1];
			$('sObjectName').value =		aParts[2];
		}
		
		function onSuggestPerson( aParams ) {
			
			var aParts = aParams.KEY.split(';');

			$('nIDPersonSalary').value = 	aParts[0];
			$('nPersonCode').value	 =	aParts[1];
			$('sPersonName').value	 =	aParts[2];
		}
		
		function onKeyPressObjectNum() {
			$('nIDObjectSalary').value = "";
			$('sObjectName').value = "";
		}
		
		function onKeyPressObjectName() {
			$('nIDObjectSalary').value = "";
			$('nObjectNum').value = "";
		}
		
		function onKeyPressPersonCode() {
			$('nIDPersonSalary').value = "";
			$('sPersonName').value = "";
		}
		
		function onKeyPressPersonName() {
			$('nIDPersonSalary').value = "";
			$('nPersonCode').value = "";
		}
		
		function changeType(sType) {
			$('td_dds').style.display = 'none';
			$('td_salary').style.display = 'none';
			$('td_fuel').style.display = 'none';
			$('td_gsm').style.display = 'none';
			
			if(sType != 'other' && sType != 'fuel') {
				var td_id = "td_" + sType;
				$(td_id).style.display = 'block';
			}
			
			if(sType == 'other' || sType == 'fuel' || sType =='gsm') {
				$('tr_doc_type').style.display = 'block';
				$('tr_doc_num').style.display = 'block';
			} else {
				$('tr_doc_type').style.display = 'none';
				$('tr_doc_num').style.display = 'none';
			}
			
			loadXMLDoc2('del_rows');
		}
		
		function onChangeDocType(val) {
			if(val == 'kvitanciq') {
				$('doc_num_req').innerHTML = '';
			} else {
				$('doc_num_req').innerHTML = '<sup>*</sup>'
			}
		}
		
		function delRow(id) {
			var ids = new Array();
			ids = id.split(',');
			$('id_row_to_del').value = ids[0];
			loadXMLDoc2('del_row',1);
			
		}
		
		function resultFromChild() {
			
			rpc_on_exit = function () {
				child_window.focus();
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2('result');
			
		}
		
		function editRow(id) {
			var ids = new Array();
			ids = id.split(',');
			var id_buy_doc = $('id_buy_doc').value;
			child_window = dialogBuyDocRow(ids[0],id_buy_doc);
		}
		function openPersonnel(id) {
			var ids = new Array();
			ids = id.split(',');
			child_window = dialogPerson(ids[1]);
		}
		
		function nextMonth(act,input_date) {
			
			var oldDate = $(input_date).value;
			var MM = oldDate.substr(0,2);
			var YY = oldDate.substr(3,4);
			
			if(act == 'next') {
				MM++;
				if(MM == '13') {
					MM = '1';
					YY++;
				}
			} else {
				MM--;
				if(MM == '0') {
					MM = '12';
					YY--;
				}
			}
			
			if(MM < 10) MM = "0" + MM;
			$(input_date).value = MM + '.' + YY;			
		}
		
		function confirmBuy() {
			loadXMLDoc2('confirm',2);
		
			rpc_on_exit = function () {
				var nIDBuyDoc = $('id_buy_doc').value;
				
				if($('open_buy_doc').value == 'true') {
					dialogBuyDocInfo(nIDBuyDoc);			
				}	
				if($('open_order').value == 'true') {
					var sParams = "id=0&doc_type=buy&id_doc=" + nIDBuyDoc;
					dialogOrder(sParams);
				}
				$('open_buy_doc').value = 'false';
				$('open_order').value = 'false';
				
				rpc_on_exit = function() {}
			}
		}
	</script>

{/literal}

<dlcalendar click_element_id="editDocDate" 		input_element_id="sDocDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" name="nIDPersonSalary" id="nIDPersonSalary" value="">	
	<input type="hidden" name="nIDObjectSalary" id="nIDObjectSalary" value="">
	<input type="hidden" name="id_buy_doc" id="id_buy_doc" value="">
	<input type="hidden" name="id_row_to_del" id="id_row_to_del" value="">
	<input type="hidden" name="open_buy_doc" id="open_buy_doc" value="false">
	<input type="hidden" name="open_order" id="open_order" value="false">
	
	<div class="page_caption">Покупка [разход]</div>
	
	<table class="input" style="margin:5px 0px 0px 0px;font-weight:bold;width:1000px;" border="0">
		<tr>
			<td style="width:200px;">
				&nbsp;
			</td>
			<td style="width:300px;">
				<fieldset style="height:160px;">
				<legend>Информация</legend>
				<table class="input">
					<tr class="even">
						<td align="right" style="width:100px;">
							За сметка на&nbsp;
						</td>
						<td>
							<select name="client_name" id="client_name" style="width:130px;"></select>
							<span style="font-weight:bold;color:red;"><sup>*</sup></span>
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Дата&nbsp;
						</td>
						<td>
							<input type="text" name="sDocDate" id="sDocDate" style="width:70px;" value="{$smarty.now|date_format:'%d.%m.%Y'}" onkeypress="return formatDate(event, '.');" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editDocDate" />
							<span style="font-weight:bold;color:red;margin-left:2px;"><sup>*</sup></span>
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Тип разход&nbsp;
						</td>
						<td>
							<select name="expense_type" id="expense_type" onchange="changeType(this.value);" style="width:100px;">
								<option value="other">Друго</option>
								<option value="dds">ДДС</option>
								<option value="salary">Заплати</option>
								<option value="fuel">Гориво</option>
								<option value="gsm">GSM</option>
							</select>
						</td>
					</tr>
					<tr class="odd" id="tr_doc_type">
						<td align="right">
							Вид документ&nbsp;
						</td>
						<td>
							<select name="doc_type" id="doc_type" style="width:130px;" onchange="onChangeDocType(this.value);">
								<option value="kvitanciq">Квитанция</option>
								<option value="faktura">Данъчна фактура</option>
								<option value="oprostena">Опростена фактура</option>
							</select>
							<span style="font-weight:bold;color:red;"><sup>*</sup></span>
						</td>
					</tr>
					<tr class="even" id="tr_doc_num">
						<td align="right">
							Номер&nbsp;
						</td>
						<td>
							<input type="text" name="doc_num" id="doc_num" style="width:100px;" onkeypress="return formatNumber(event);">
							<span id="doc_num_req" style="font-weight:bold;color:red;"></span>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td style="width:20px;">
				&nbsp;
			</td>
			<td valign="top" style="width:300px;display:none;" id="td_dds">
				<fieldset>
				<legend>ДДС към бюджета</legend>
				<table class="input">
					<tr class="even">
						<td align="right">
							За месец&nbsp;
						</td>
						<td>
							<img src="images/mleft.gif" onclick="nextMonth('prev','dateDDS');" style="cursor:pointer;">
							<input 
								style="width:50px;" 
								id="dateDDS" 
								name="dateDDS" 
								type="text" 
								class="clear"  
								maxlength="7" 
								readonly 
								title="ММ.ГГГГ" 
								value={$smarty.now|date_format:'%m.%Y'}
							>
							<img src="images/mright.gif" onclick="nextMonth('next','dateDDS');" style="cursor:pointer;">
						</td>
					</tr>
					<tr class="odd">
						<td colspan="2" style="padding-left:50px;">
							Входящ номер на декларацията по ДДС
						</td>
					</tr>
					<tr class="even">
						<td colspan="2" style="padding-left:50px;">
							<input name="dds_in_num" id="dds_in_num" onkeypress="return formatNumber(event);">
						</td>
					</tr>
					<tr>
						<td colspan="2" align="right">
							<button onclick="loadXMLDoc2('loadInventoryDDS');" style="width:50px;"><img src="images/confirm.gif">Опис</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td valign="top" style="width:400px;display:none;" id="td_salary">
				<fieldset>
				<legend>Заплати</legend>
				<table class="input">
					<tr class="even">
						<td style="width:70px;" align="right">
							Фирма&nbsp;
						</td>
						<td>
							<select 
								name="nIDFirmSalary" 
								id="nIDFirmSalary" 
								onchange="loadXMLDoc2('loadOfficesSalary');"
								style="width:150px;"
							>
							</select>
						</td>
						<td align="center">
							За месец
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr class="odd">
						<td style="width:70px;" align="right">
							Регион&nbsp;
						</td>
						<td>
							<select 
								name="nIDOfficeSalary" 
								id="nIDOfficeSalary"
								style="width:150px;"
							>
							</select>
						</td>
						<td align="center">
							<img src="images/mleft.gif" onclick="nextMonth('prev','dateSalary');" style="cursor:pointer;">
							<input 
								style="width:50px;" 
								id="dateSalary" 
								name="dateSalary" 
								type="text" 
								class="clear"  
								maxlength="7" 
								readonly 
								title="ММ.ГГГГ" 
								value={$smarty.now|date_format:'%m.%Y'}
							>
							<img src="images/mright.gif" onclick="nextMonth('next','dateSalary');" style="cursor:pointer;">
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
				</table>
				<table class="input">
					<tr class="even">
						<td style="width:70px;" align="right">
							Обект&nbsp;
						</td>
						<td>
							<input 
								type="text" 
								id="nObjectNum" 
								name="nObjectNum" 
								style="width: 100px; text-align: right;" 
								suggest="suggest" 
								queryType="objByNum" 
								onkeypress="formatDigits( event )" 
								maxlength="12" 
							>&nbsp;
							<input 
								type="text" 
								id="sObjectName" 
								name="sObjectName" 
								style="width: 200px;" 
								suggest="suggest" 
								queryType="objByName" 
							>
						</td>
					</tr>
					<tr class="odd">
						<td style="width:70px;" align="right">
							Служител&nbsp;
						</td>
						<td>
							<input 
								type="text" 
								id="nPersonCode" 
								name="nPersonCode" 
								style="width: 100px; text-align: right;" 
								suggest="suggest" 
								queryType="personByCode" 
								onkeypress="formatDigits( event )" 
								maxlength="12" 
							>&nbsp;
							<input 
								type="text" 
								id="sPersonName" 
								name="sPersonName" 
								style="width: 200px;" 
								suggest="suggest" 
								queryType="personByName" 
							>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="right">
							<button style="width:50px;" onclick="loadXMLDoc2('loadInventory');"><img src="images/confirm.gif">Опис</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td valign="top" style="width:300px;display:none;" id="td_fuel">
				<fieldset>
				<legend>Гориво</legend>
				<table class="input">
					<tr class="even">
						<td style="width:50px;" align="right">От&nbsp;</td>
						<td align="left">
							<input type="text" name="sFromDate" id="sFromDate" style="width:70px;" onkeypress="return formatDate(event, '.');" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editFromDate" />
						</td>
						<td>До</td>
						<td align="left">
							<input type="text" name="sToDate" id="sToDate" style="width:70px;" onkeypress="return formatDate(event, '.');" />
							&nbsp;
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editToDate" />
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Фирма&nbsp;
						</td>
						<td colspan="3">
							<select 
								name="nIDFirmFuel" 
								id="nIDFirmFuel" 
								onchange="loadXMLDoc2('loadOfficesFuel');"
								style="width:150px;"
							>
							</select>
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Регион&nbsp;
						</td>
						<td colspan="3">
							<select 
								name="nIDOfficeFuel" 
								id="nIDOfficeFuel"
								style="width:150px;"
							>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="4" align="right">
							<button style="width:50px;"><img src="images/confirm.gif">Опис</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td valign="top" style="width:300px;display:none;" id="td_gsm">
				<fieldset>
				<legend>GSM</legend>
				<table class="input">
					<tr class="even">
						<td align="center">
							За месец
						</td>
						<td style="width:50px;">
							&nbsp;
						</td>
					</tr>
					<tr class="odd">
						<td align="center">
							<img src="images/mleft.gif" onclick="nextMonth('prev','dateGSM');" style="cursor:pointer;">
							<input 
								style="width:50px;" 
								id="dateGSM" 
								name="dateGSM" 
								type="text" 
								class="clear"  
								maxlength="7" 
								readonly 
								title="ММ.ГГГГ" 
								value={$smarty.now|date_format:'%m.%Y'}
							>
							<img src="images/mright.gif" onclick="nextMonth('next','dateGSM');" style="cursor:pointer;">
						</td>
						<td style="width:50px;">
							&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan="2" align="right">
							<button onclick="loadXMLDoc2('loadInventoryGSM');" style="width:50px;"><img src="images/confirm.gif">Опис</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
	</table>
	<table class="input">
		<tr>
			<td>
				<fieldset>
				<legend>Опис</legend>
				<table class="input">
					<tr>
						<td style="width:80px;background-color:#ccffcc;">
							ОБЩА СУМА
						</td>
						<td style="width:120px;">
							<input 
								type="text" 
								id="total_sum" 
								name="total_sum" 
								style="width:80px;text-align:right;"
								onkeypress="return formatMoney(event);"
							> лв.
						</td>
						<td>
							<button onclick="loadXMLDoc2('edit_inventory')"><img src="images/confirm.gif">Коригирай описа</button>
						</td>
						<td align="right">
							<button onclick="editRow(0);" class="input"><img src="images/confirm.gif">Добави</button>
						</td>
					</tr>
				</table>
				<table class="input">
					<tr>
						<td>
							<div id="result" rpc_excel_panel="off" rpc_resize="off" style="height:300px;width:985px;overflow:auto;"></div>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<table class="input" style="margin-top:10px;">
		<tr>
			<td align="right">
				<button onclick="confirmBuy();" class="search"><img src="images/confirm.gif">Потвърди</button>
				<button onclick="parent.window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
	</table>
</form>

<script>
	onInit();
</script>