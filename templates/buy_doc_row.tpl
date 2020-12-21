{literal}

	<script>
		rpc_debug = true;
		
		function onInit() {
			
			attachEventListener( $('nObjectNum'),  "keypress", onKeyPressObjectNum);
			attachEventListener( $('sObjectName'), "keypress", onKeyPressObjectName);
			
			InitSuggestForm = function() {
				for(var i=0; i<suggest_elements.length; i++) {
					switch( suggest_elements[i]['id'] ) {
						case 'nObjectNum':
						case 'sObjectName':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
					}
				}
			}
			
			loadXMLDoc2('load');
		}
		
		function onSuggestObject( aParams ) {
			var aParts = aParams.KEY.split(';');
			
			$('id_object').value = 		aParts[0];
			$('nObjectNum').value = 	aParts[1];
			$('sObjectName').value =	aParts[2];
		}
		
		function onKeyPressObjectNum() {
			$('id_object').value = "";
			$('sObjectName').value = "";
		}
		
		function onKeyPressObjectName() {
			$('id_object').value = "";
			$('nObjectNum').value = "";
		}
		
		function countSum() {
			var nSinglePrice = $('single_price').value;
			var nQuantity = $('quantity').value;
			
			if(nSinglePrice >= 0 && nQuantity >= 0) {
				$('total_sum').value = Math.round(nSinglePrice*nQuantity*1000)/1000;
			}
		}
		
//		function nextMonth(act,input_date) {
//			
//			var oldDate = $(input_date).value;
//			var MM = oldDate.substr(0,2);
//			var YY = oldDate.substr(3,4);
//			
//			if(act == 'next') {
//				MM++;
//				if(MM == '13') {
//					MM = '1';
//					YY++;
//				}
//			} else {
//				MM--;
//				if(MM == '0') {
//					MM = '12';
//					YY--;
//				}
//			}
//			
//			if(MM < 10) MM = "0" + MM;
//			$(input_date).value = MM + '.' + YY;			
//		}
		
		function addRow() {
			
			rpc_on_exit = function () {
				if(window.opener && !window.opener.closed)
					if(window.opener.loadXMLDoc) {
						window.opener.resultFromChild();
					}
			}
			
			loadXMLDoc2('save');
		}
		
		function onchangeFirm() {
			
			$('id_object').value = '';
			$('nObjectNum').value = '';
			$('sObjectName').value = '';
			
			if($('radio_checked').value == 'list') {
				rpc_on_exit = function () {
					rpc_on_exit = function () {};
					loadXMLDoc2('list_objects');
				}
			}
			
			loadXMLDoc2('loadOffices');
		}
		
		function onchangeRegion() {
			$('id_object').value = '';
			$('nObjectNum').value = '';
			$('sObjectName').value = '';
			
			if($('radio_checked').value == 'list') {
				loadXMLDoc2('list_objects');
			}
			
		}
		
		function onchangeObject() {
			$('id_object').value = $('nIDObject').value;
		}
		
		function onclickRadio(type) {
			
			$('id_object').value = 0;
			$('nIDObject').value = '';
			$('nObjectNum').value = '';
			$('sObjectName').value = '';
			
			switch(type) {
				case "suggest":
					$('tr_suggest').style.display = 'block';
					$('tr_list').style.display = 'none';
					$('radio_checked').value = 'suggest';
				break;
				case "list":
					$('tr_suggest').style.display = 'none';
					$('tr_list').style.display = 'block';
					$('radio_checked').value = 'list'
					loadXMLDoc2('list_objects');
				break;
			}
			
			
		}
		
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<input type="hidden" name="nIDBuyDoc" id="nIDBuyDoc" value="{$nIDBuyDoc}">
	<input type="hidden" name="id_object" id="id_object" value="">
	<input type="hidden" name="radio_checked" id="radio_checked" value="suggest">
	
	<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} ред в описа</div>

	<table class="input" style="margin:10px 20px 0px 15px;width:450px;" border="0">
		<tr class="even">
			<td>
				<table class="input">
					<tr>
						<td align="right">
							Фирма&nbsp;
						</td>
						<td>
							<select name="nIDFirm" id="nIDFirm" style="width:150px;" onchange="onchangeFirm();"></select>
						</td>
						<td align="right">
							Регион&nbsp;
						</td>
						<td>
							<select name="nIDOffice" id="nIDOffice" style="width:150px;" onchange="onchangeRegion();"></select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="center">
				<fieldset style="width:400px;height:80px;padding-top:5px;">
				<legend>Избор на обект (Незадължителен)</legend>
				<table class="input" border="0">
					<tr>
						<td align="right" style="width:100px;">
							<input type="radio" name="radio_object" value="suggest" onclick="onclickRadio(this.value);" checked class="clear">
						</td>
						<td>
							Чрез подсказка
						</td>
						<td align="right">
							<input type="radio" name="radio_object" value="list" onclick="onclickRadio(this.value);" class="clear"> 
						</td>
						<td style="width:150px;">
							Чрез списък
						</td>
					</tr>
					<tr id="tr_suggest" class="even">
						<td colspan="4" align="center">
							<input 
								type="text" 
								id="nObjectNum" 
								name="nObjectNum" 
								style="width: 100px; text-align: right;" 
								suggest="suggest" 
								queryType="objByNum" 
								queryParams="nIDFirm;nIDOffice"
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
								queryParams="nIDFirm;nIDOffice"
							>
						</td>
					</tr>
					<tr id="tr_list" class="odd" align="center" style="display:none;">
						<td colspan="4">
							<select id="nIDObject" name="nIDObject" style="width:300px;" onchange="onchangeObject();"></select>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td>
				<table class="input">
					
					<tr class="even">
						<td align="right">
							Разход&nbsp;
						</td>
						<td colspan="2">
							<select name="nIDNomenclatureExpense" id="nIDNomenclatureExpense" style="width:250px;"></select>
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Месец&nbsp;
						</td>
						<td>
							<select id="dateM" name="dateM" style="width: 80px;" />
						</td>
						<td rowspan="4" style="width:250px;">
							<fieldset style="height:60px;">
							<legend>Бележка</legend>
							<textarea name="note" id="note" style="height:100%;width:100%;margin: 5px 5px 5px 5px;"></textarea>
							</fieldset>
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Ед. цена&nbsp;
						</td>
						<td>
							<input type="text" name="single_price" id="single_price" onkeypress="return formatMoney(event);" onkeyup="countSum();" style="text-align:right;width:80px;"> лв.
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Бройка&nbsp;
						</td>
						<td>
							<input type="text" name="quantity" id="quantity" value="1" onkeypress="return formatNumber(event);" onkeyup="countSum();" style="text-align:right;width:80px;">
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Обща сума&nbsp;
						</td>
						<td>
							<input type="text" name="total_sum" id="total_sum" style="text-align:right;width:80px;" readonly> лв.
						</td>
					</tr>
					<tr>
						<td colspan="3" align="right" style="padding-top:10px;">
							<button onclick="addRow();" class="search"><img src="images/confirm.gif">Добави</button>
							<button onclick="parent.window.close();"><img src="images/cancel.gif">Затвори</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
</form>

<script>
	onInit();
</script>