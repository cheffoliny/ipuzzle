{literal}

	<script>
		rpc_debug = true;
		
		function onInit() {
			
			attachEventListener( $('client_ein'),  "keyup", onKeyUpClientEin);
			attachEventListener( $('client_ein_dds'),  "keyup", onKeyUpClientEinDDS);
			attachEventListener( $('deliverer_ein'),  "keyup", onKeyUpDelivererEin);
			attachEventListener( $('deliverer_ein_dds'),  "keyup", onKeyUpDelivererEinDDS);
			
			InitSuggestForm = function() {
				for(var i=0; i<suggest_elements.length; i++) {
					switch(suggest_elements[i]['id']) {
						case 'client_name':
						case 'client_address':
						case 'client_ein':
						case 'client_ein_dds':
						case 'client_mol':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestClient );
						break;
						case 'deliverer_name':
						case 'deliverer_address':
						case 'deliverer_ein':
						case 'deliverer_ein_dds':
						case 'deliverer_mol':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestDeliverer);
						break;
					}
				}
			}
			
			loadXMLDoc2('result');
		}
		
		function onSuggestClient(aParams) {
	
			var aParts = aParams.KEY.split(";;");
			
			$('client_name').value = aParts[1];
			$('client_address').value = aParts[2];
			$('client_ein').value = aParts[3];
			$('client_ein_dds').value = aParts[4];
			$('client_mol').value = aParts[5];
		}
		
		function onSuggestDeliverer(aParams) {			
			var aParts = aParams.KEY.split(";;");
			
			$('id_deliverer').value = aParts[0];
			$('deliverer_name').value = aParts[1];
			$('deliverer_address').value = aParts[2];
			$('deliverer_ein').value = aParts[3];
			$('deliverer_ein_dds').value = aParts[4];
			$('deliverer_mol').value = aParts[5];
		}
		
		function changeView(obj) {
			$('sViewType').value = obj.value;
			loadXMLDoc2('change_view');
		}
		
		function delDoc() {
			if(confirm("Наистина ли желаете да анулирате документа")) {
				loadXMLDoc2('del_doc',1);
			}
		}
		
		function onKeyUpClientEin() {
			if( $('client_ein').value == '' || validateEIN($('client_ein').value)) {
				$('client_ein').style.backgroundColor = '#ffffcc';
			} else {
				$('client_ein').style.backgroundColor = '#ff8888';
			}
		}
		
		function onKeyUpClientEinDDS() {
			if( $('client_ein_dds').value == '' || validateEinDDS($('client_ein_dds').value)) {
				$('client_ein_dds').style.backgroundColor = '#ffffcc';
			} else {
				$('client_ein_dds').style.backgroundColor = '#ff8888';
			}
		}
		
		function onKeyUpDelivererEin() {
			if( $('deliverer_ein').value == '' || validateEIN($('deliverer_ein').value)) {
				$('deliverer_ein').style.backgroundColor = '#ffffcc';
			} else {
				$('deliverer_ein').style.backgroundColor = '#ff8888';
			}
		}
		
		function onKeyUpDelivererEinDDS() {
			if( $('deliverer_ein_dds').value == '' || validateEinDDS($('deliverer_ein_dds').value)) {
				$('deliverer_ein_dds').style.backgroundColor = '#ffffcc';
			} else {
				$('deliverer_ein_dds').style.backgroundColor = '#ff8888';
			}
		}
		
		function validates() {
			if( $('client_ein').value != '' && !validateEIN($('client_ein').value)) {
				alert("Невалидна стойност на полето клиент ЕИН");
			} else if( $('client_ein_dds').value != '' && !validateEinDDS($('client_ein_dds').value)) {
				alert("Невалидна стойност на полето клиент ЕИН ДДС")
			} else if( $('deliverer_ein').value != '' && !validateEIN($('deliverer_ein').value)) {
				alert("Невалидна стойност на полето доставчик ЕИН");
			} else if( $('deliverer_ein_dds').value != '' && !validateEinDDS($('deliverer_ein_dds').value)) {
				alert("Невалидна стойност на полето доставчик ЕИН ДДС");
			} else {
				return true;
			}
			return false;
		}
		
		function finalDoc() {
			if(validates()) {
				$('confirm_click').value = 'true';
				loadXMLDoc2('save',1);
			}
		}
		
		function saveForm() {
			if(validates()) {
				loadXMLDoc2('save',1);
			}
		}
		
		rpc_on_exit = function() {
			
			onKeyUpClientEin();
			onKeyUpClientEinDDS();
			onKeyUpDelivererEin();
			onKeyUpDelivererEinDDS();
			
			if($('open_order').value == 'true') {
				var sParams = "id=0&doc_type=buy&id_doc=" + $('nID').value;
				dialogOrder(sParams);
				$('open_order').value = "false";
			}
		}
		
	</script>

{/literal}

<dlcalendar id="calender" click_element_id="editDocDate" 	input_element_id="sDocDate" start_date="{$nMinusSevenDays}"	tool_tip="Изберете дата"></dlcalendar>

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	<input type="hidden" id="sViewType" name="sViewType" value="">
	<input type="hidden" id="sDocStatus" name="sDocStatus" value="{$sDocStatus}">
	<input type="hidden" id="id_deliverer" name="id_deliverer" value="">
	<input type="hidden" id="confirm_click" name="confirm_click" value="false">
	<input type="hidden" id="open_order" name="open_order" value="false">
	
	<div id="page_caption" class="page_caption">{$sPageCaption|default:''}</div>

	<table width="100%" cellpadding="0" cellspacing="0" id="filter">
		<tr>
			<td>
				{include file='buy_doc_tabs.tpl'}
			</td>
		</tr>
	</table>
	
	<table border="0" style="width:780px;font-weight:bold;font-size:12px;margin:5px 15px 0px 15px;">
	
		<tr>
			<td style="width:360px;">
				<fieldset id="fieldset1">
				<legend>Клиент</legend>
				<table class="input" style="margin:0px 5px 5px 5px;">
					<tr class="even">
						<td>
							Име
						</td>
						<td>
							<input type="text" name="client_name" id="client_name" suggest="suggest" queryType="deliverer" queryParams="deliverer_name" style="width:300px;">
						</td>
					</tr>
					<tr class="odd">
						<td>
							Адрес
						</td>
						<td>
							<input type="text" name="client_address" id="client_address" suggest="suggest" queryType="deliverer" queryParams="deliverer_address"  style="width:300px;">
						</td>
					</tr>
					<tr class="even">
						<td>
							ИН
						</td>
						<td>
							<input type="text" name="client_ein" id="client_ein" suggest="suggest" queryType="deliverer" queryParams="deliverer_ein" style="width:300px;">
						</td>
					</tr>
					<tr class="odd">
						<td>
							ИН&nbsp;<span style="font-size:8px;">по ДДС</span>
						</td>
						<td>
							<input type="text" name="client_ein_dds" id="client_ein_dds" suggest="suggest" queryType="deliverer" queryParams="deliverer_ein_dds" style="width:300px;">
						</td>
					</tr>
					<tr class="even">
						<td>
							МОЛ
						</td>
						<td>
							<input type="text" name="client_mol" id="client_mol" suggest="suggest" queryType="deliverer" queryParams="deliverer_mol" style="width:300px;" >
						</td>
					</tr>
					
				</table>
					
				</fieldset>
			
			</td>
			<td style="width:360px;">
				<fieldset>
				<legend>Доставчик</legend>
				<table class="input" style="margin:0px 5px 5px 5px;">
					<tr class="even">
						<td>
							Име
						</td>
						<td>
							<input type="text" name="deliverer_name" id="deliverer_name" suggest="suggest" queryType="client" queryParams="client_name" style="width:300px;">
						</td>
					</tr>
					<tr class="odd">
						<td>
							Адрес
						</td>
						<td>
							<input type="text" name="deliverer_address" id="deliverer_address" suggest="suggest" queryType="client" queryParams="client_address" style="width:300px;">
						</td>
					</tr>
					<tr class="even">
						<td>
							ИН
						</td>
						<td>
							<input type="text" name="deliverer_ein" id="deliverer_ein" suggest="suggest" queryType="client" queryParams="client_ein" style="width:300px;">
						</td>
					</tr>
					<tr class="odd">
						<td>
							ИН <span style="font-size:8px;">по ДДС</span>
						</td>
						<td>
							<input type="text" name="deliverer_ein_dds" id="deliverer_ein_dds" suggest="suggest" queryType="client" queryParams="client_ein_dds" style="width:300px;">
						</td>
					</tr>
					<tr class="even">
						<td>
							МОЛ
						</td>
						<td>
							<input type="text" name="deliverer_mol" id="deliverer_mol" suggest="suggest" queryType="client" queryParams="client_mol" style="width:300px;">
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		
		</tr>
	</table>
	<hr>
	<table class="input">
		<tr>
			<td>
				<table class="input" border="0">
					<tr>
						<td align="right">
							Статус:
						</td>
						<td>
							<input type="text" name="paid_status" id="paid_status" class="clear" style="width:100px;" readonly>
						</td>
						<td align="right">
							Дата:&nbsp;
						</td>
						<td align="left">
							<input type="text" name="sDocDate" id="sDocDate" class="inp100" onkeypress="return formatDate(event, '.');" />&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editDocDate" />
						</td>
						<td align="right">
							Плащане:&nbsp;
						</td>
						<td>
							<select name="paid_type" id="paid_type" style="width:100px;" >
								<option value="cash">в брой</option>
								<option value="bank">по банка</option>
							</select>
						</td>
						<td style="background-color:#ccffcc;width:100px;padding-left:10px;">
							СУМА
						</td>
						<td style="background-color:#eeeeee;">
							<input type="text" name="sum_total" id="sum_total" class="clear" style="text-align:right;font-weight:bold;width:100px;" readonly>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<hr style="margin:0;padding:0;">
	<table class="input" border="0">
		<tr>
			<td>
				<table class="input">
					<tr class="even">
						<td align="right">
							Създал:
						</td>
						<td>
							<input type="text" class="clear" name="created_user" id="created_user" style="width:300px;" readonly>
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Редактирал:
						</td>
						<td>
							<input type="text" class="clear" name="updated_user" id="updated_user" style="width:300px;" readonly>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<table class="input">
					<tr>
						<td style="width:400px;">
							<fieldset>
							<legend>Забележка</legend>
							<table class="input">
								<tr>
									<td>
										<textarea name="note" id="note" style="width:390px;"></textarea>
									</td>
								</tr>
							</table>
							</fieldset>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<hr>
	<table class="input">
		<tr>
			<td>
				<button id="b_del" onclick="delDoc();"><img src="images/bin.gif">Анулирай</button>
			</td>
			<td align="right">
				<button id="b_confirm" onclick="finalDoc();"><img src="images/confirm.gif">Потвърди</button>
			</td>
			<td>
				<div id="izvestie"></div>
			</td>
			<td align="right">
				<button id="b_save" onclick="saveForm();" class="search"><img src="images/confirm.gif">Запиши</button>
				<button onclick="window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
	</table>
</form>

<script>
	onInit();
</script>