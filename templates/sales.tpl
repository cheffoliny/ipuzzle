{literal}

	<script>
		
		rpc_debug = true;
		rpc_html_debug = true;
	
		function onInit() {
			
			attachEventListener( $('client_eik'), "keypress",	function () {resetClient('client_eik')});
			attachEventListener( $('client_name'), "keypress",	function () {resetClient('client_name')});
			attachEventListener( $('client_phone'), "keypress",	function () {resetClient('client_phone')});
			attachEventListener( $('client_ein'), "keypress",	function () {resetClient('client_ein')});
			
			attachEventListener( $('object_num'), "keypress",	function () {resetObject('object_num')});
			attachEventListener( $('object_name'), "keypress",	function () {resetObject('object_name')});
			attachEventListener( $('object_mol'), "keypress",	function () {resetObject('object_mol')});
			attachEventListener( $('object_address'), "keypress",	function () {resetObject('object_address')});
			
			attachEventListener( $('doc_client_ein'), "keypress",	function () {resetDocClient('doc_client_ein')});
			attachEventListener( $('doc_client_name'), "keypress",	function () {resetDocClient('doc_client_name')});
			
			InitSuggestForm = function() {
				for(var i=0; i<suggest_elements.length; i++) {
					switch(suggest_elements[i]['id']) {
						case 'client_eik':
						case 'client_name':
						case 'client_phone':
						case 'client_ein':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestClient );
						break;
						case 'object_num':
						case 'object_name':
						case 'object_mol':
						case 'object_address':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
						case 'doc_client_ein':
						case 'doc_client_name':
							suggest_elements[i]['suggest'].setSelectionListener( onSuggestDocClient );
						break;
					}
				}
			}
			
			loadXMLDoc2('init');
		}
		
		function onSuggestClient(aParams) {
	
			var aParts = aParams.KEY.split(";;");
			
			$('client_eik').value = aParts[0];
			$('client_name').value = aParts[1];
			$('client_phone').value = aParts[6];
			$('client_ein').value = aParts[3];
		}
		
		function onSuggestObject(aParams) {
			var aParts = aParams.KEY.split(";;");
			
			$('id_object').value = aParts[0];
			$('object_num').value = aParts[1];
			$('object_name').value = aParts[2];
			$('object_mol').value = aParts[4];
			$('object_address').value = aParts[3];
			
			loadXMLDoc2('getPayment');
		}
		
		function onSuggestDocClient(aParams) {
			var aParts = aParams.KEY.split(";;");
			
			$('id_client').value = aParts[0];
			$('doc_client_ein').value = aParts[3];
			$('doc_client_name').value = aParts[1];
			
			$('doc_num').value = '';
		}
		
		function resetObject(input_name) {
			var aObjectInputs = new Array("object_num","object_name","object_mol","object_address","min_paid");
			
			for(var i=0 ; i < aObjectInputs.length ; i++) {
				if(aObjectInputs[i] != input_name) {
					$(aObjectInputs[i]).value = '';
				}
			}

			$('id_object').value = '';
		}
		
		function resetClient(input_name) {
			var aClientInputs = new Array("client_eik","client_name","client_phone","client_ein");
			
			for(var i=0 ; i < aClientInputs.length ; i++) {
				if(aClientInputs[i] != input_name) {
					$(aClientInputs[i]).value = '';
				}
			}
			if(input_name != 'client_eik') {
			}
		}
		
		function resetDocClient(input_name) {
			var aDocClientInputs = new Array("doc_num","doc_client_name","doc_client_ein");
			
			for(var i = 0 ; i < aDocClientInputs.length ; i++ ) {
				if(aDocClientInputs[i] != input_name) {
					$(aDocClientInputs[i]).value = '';
				}
			}
			
			$('id_client').value = '';
		}
		
		function clickDocNum() {
			$('id_client').value = '';
			$('doc_client_name').value = '';
			$('doc_client_ein').value = '';
		}
		
		function focusBocus(obj) {
			$('by_client').filters.alpha.opacity = "30";
			$('by_object').filters.alpha.opacity = "30";
			$('by_doc').filters.alpha.opacity = "30";
			
			obj.filters.alpha.opacity = "100";
			$('search_type').value = obj.id;
			if(obj.id == 'by_object') {
				resetClient('');
				resetDocClient('');
			} else if( obj.id == 'by_client') {
				resetObject('');
				resetDocClient('');
			} else if( obj.id == 'by_doc') {
				resetClient('');
				resetObject('');
			}
		}
		
		function nextMonth(act) {
			
			var oldDate = $('dateM').value;
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
			$('dateM').value = MM + '.' + YY;			
		}
		
		function makeInvoice() {
			
			loadXMLDoc2('faktura');
			
			rpc_on_exit = function () {
				
				var nIDSaleDoc = $('id_sale_doc').value;
				
				if(nIDSaleDoc != 0) {
					dialogSaleDocInfo2(nIDSaleDoc);
				}
				
				$('id_sale_doc').value = '0';
			}
		}
		
		function makeInvoice2() {
			//Pavel
			var e 	= document.getElementsByTagName("input");
			var str = '';
			var cl 	= $('id_client').value;
			
			for ( var i = 0; i < e.length; i++ ) {
				if ( e[i].type == 'checkbox' ) {
					if ( e[i].checked == true ) {
						e[i].disabled = true;
						
						if ( str.length > 0 ) {
							str += ';;' + e[i].name;
						} else {
							str = e[i].name;
						}
					}
				}
			}
			
			if ( str.length > 0 ) {
				dialogSaleDocInfo2(0, str, cl);
			} else {
				alert('Няма избрани задължения!');
			}
		}		
		
		function changePrice(obj) {
			//Pavel
			if ( obj.checked == true ) {

				if ( obj.id.length > 11 ) {
					var date = obj.id.substr(obj.id.length - 11, 10);
					var e = document.getElementsByTagName("input");
						
					for ( var i = 0; i < e.length; i++ ) {

						if ( e[i].type == 'checkbox' ) {
							var date2 = e[i].id.substr(e[i].id.length - 11, 10);
													
							if ( date2 < date ) {
								if ( e[i].checked == false ) {
									e[i].checked = true; 
									increasePrice(e[i]);
								}
							}
						}
					}
					
					
				
				}
				
				var nSum 		= parseFloat(obj.parentNode.total_sum);
				var nSumAll 	= parseFloat($('sum_all').value);	
				var nSumAllNew 	= nSumAll + nSum;					
			} else {
				if ( obj.id.length > 11 ) {
					var date = obj.id.substr(obj.id.length - 11, 10);
					var e = document.getElementsByTagName("input");
										
					for ( var i = 0; i < e.length; i++ ) {
						
						if ( e[i].type == 'checkbox' ) {
							var date2 = e[i].id.substr(e[i].id.length - 11, 10);
						
							if ( date2 > date ) {
								if ( e[i].checked == true ) {
									e[i].checked = false; 
									decreasePrice(e[i]);
								}								
							}
						}
					}
				}					
				
				var nSum 		= parseFloat(obj.parentNode.total_sum);
				var nSumAll 	= parseFloat($('sum_all').value);					
				var nSumAllNew 	= nSumAll - nSum;
			}
			
			nSumAllNew = Math.round(nSumAllNew * 100) / 100;
			nSumAllNew = nSumAllNew.toFixed(2);
			$('sum_all').value = nSumAllNew + " лв.";	
		}
		
		// Pavel
		function increasePrice(obj) {
			var nSumRow 		= parseFloat(obj.parentNode.total_sum);
			var nSumTotal 		= parseFloat($('sum_all').value);			
			var nSumTotalNew 	= nSumTotal + nSumRow;

			nSumTotalNew 		= Math.round(nSumTotalNew * 100) / 100;
			nSumTotalNew 		= nSumTotalNew.toFixed(2);
			$('sum_all').value 	= nSumTotalNew + " лв.";
		}
		
		// Pavel
		function decreasePrice(obj) {
			var nSumRow 		= parseFloat(obj.parentNode.total_sum);
			var nSumTotal 		= parseFloat($('sum_all').value);			
			var nSumTotalNew 	= nSumTotal - nSumRow;

			nSumTotalNew 		= Math.round(nSumTotalNew * 100) / 100;
			nSumTotalNew 		= nSumTotalNew.toFixed(2);
			$('sum_all').value 	= nSumTotalNew + " лв.";
		}		
		
		function makeReceipt() {
			$('simple').value = 0;
			
			loadXMLDoc2('receipt');
			
			rpc_on_exit = function () {
				
				var sIDDocs = $('sIDDocs').value;
				
				if ( sIDDocs != '' ) {
					dialogSalesPayOrders(sIDDocs);
				}
				
				$('sIDDocs').value = '';
			}
		}
		
		function makeReceipt2() {
		
			if ( confirm("Наистина ли желаете да платите с опростена квитанция?") ) {
				$('simple').value = 1;
				
				loadXMLDoc2('receipt');
				
				rpc_on_exit = function () {
					
					var sIDDocs = $('sIDDocs').value;
					
					if ( sIDDocs != '' ) {
						dialogSalesPayOrders(sIDDocs, 1);
					}
					
					$('sIDDocs').value = '';
				}
			}
		}		
		
		function openSaleDoc(id) {
			dialogSaleDocInfo2(id);
		}
		
		function openOrder(id) {
			
			var sParams = "id_sale_doc=" + id + "&id=0";
			
			dialogOrder(sParams);
		}
		
		function formSubmit() {
			if($('sfield'))	$('sfield').value = "";
			$('sum_all').value = '';
			
			if($('search_type').value == 'by_doc') {
				$('buttons').style.display = 'none';
			} else {
				$('buttons').style.display = 'block';
			}
			
			loadXMLDoc2('result');
		}
		
		function openObject() {
			var obj 	= $('id_object').value;
			var prms 	= new String();
			
			if ( parseInt(obj) > 0 ) {
				prms = 'nID=' + obj;
				dialogObjectInfo( prms );
			}
		}
		function openClient() {
			var id 	= $('client_eik').value;

			if ( parseInt(id) > 0 ) {
				dialogClientInfo( id );
			}
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" name="search_type" id="search_type" value="by_client">	
	<input type="hidden" name="id_object" id="id_object" value="">
	<input type="hidden" name="id_client" id="id_client" value="">
	<input type="hidden" name="id_sale_doc" id="id_sale_doc" value="0">
	<input type="hidden" name="sIDDocs" id="sIDDocs" value="">
	<input type="hidden" name="simple" id="simple" value="0">
	
	<div class="page_caption">Продажба [приход]</div>
	
	<table class="input" style="font-weight:bold;" cellspacing="5">
		<tr>
			<td>
				<fieldset id="by_client" onclick="focusBocus(this);" style="filter:alpha(opacity=100);">
				<legend>Търсене по клиент</legend>
				<table class="input" style="margin-bottom:3px;">
					<tr class="even">
						<td>
							ЕИК&nbsp;<span style="font-weight:bold;color:red;"><sup>*</sup></span>
						</td>
						<td>
							<table cellspacing="0" cellpadding="0" style="font-size: 0px;">
							<tbody>
								<tr>
									<td>
										<input type="text" name="client_eik" id="client_eik" style="width:210px;" suggest="suggest" queryType="client" queryParams="client_eik">
									</td>
									<td style="width: 20px; text-align: center;">
										&nbsp;&nbsp;<img src="images/history.gif" style="cursor: hand;" onclick="openClient();" title="Картон на клиент" border="0">
									</td>
								</tr>
							</tbody>
							</table>
						</td>
					</tr>
					<tr class="odd">
						<td>
							Име
						</td>
						<td>
							<input type="text" name="client_name" id="client_name" style="width:230px;" suggest="suggest" queryType="client" queryParams="client_name">
						</td>
					</tr>
					<tr class="even">
						<td>
							Телефон
						</td>
						<td>
							<input type="text" name="client_phone" id="client_phone" style="width:230px;" suggest="suggest" queryType="client" queryParams="client_phone">
						</td>
					</tr>
					<tr class="odd">
						<td>
							ЕИН
						</td>
						<td>
							<input type="text" name="client_ein" id="client_ein" style="width:230px;" suggest="suggest" queryType="client" queryParams="client_ein">
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td>
				<fieldset id="by_object" onclick="focusBocus(this);" style="filter:alpha(opacity=30);" >
				<legend>Търсене по обект</legend>
				<table class="input" style="margin-bottom:3px;">
					<tr class="even">
						<td>
							Номер
						</td>
						<td>
							<table cellspacing="0" cellpadding="0" style="font-size: 0px;">
								<tr>
									<td>
										<input type="text" name="object_num" id="object_num" style="width: 50px;" suggest="suggest" queryType="objectComplex" queryParams="object_num;nIDStatus" />
									</td>
									
									<td style="width: 20px; text-align: center;">
										&nbsp;&nbsp;<img src="images/history.gif" style="cursor: hand;" onclick="openObject();" border="0" title="Картон на обект" />&nbsp;&nbsp;
									</td>
									
									<td>
										<input type="text" name="min_paid" id="min_paid" style="width: 50px;" readonly />&nbsp;
									</td>	
									
									<td>
										&nbsp;&nbsp;<select id="nIDStatus" name="nIDStatus" style="width: 108px;"></select>
									</td>	
								</tr>
							</table>
						</td>
					</tr>
					<tr class="odd">
						<td>
							Име
						</td>
						<td>
							<input type="text" name="object_name" id="object_name" style="width:230px;" suggest="suggest" queryType="objectComplex" queryParams="object_name;nIDStatus">
						</td>
					</tr>
					<tr class="even">
						<td>
							МОЛ
						</td>
						<td>
							<input type="text" name="object_mol" id="object_mol" style="width: 230px;" suggest="suggest" queryType="objectComplex" queryParams="object_mol;nIDStatus">
						</td>
					</tr>
					<tr class="odd">
						<td>
							Адрес
						</td>
						<td>
							<input type="text" name="object_address" id="object_address" style="width:230px;" suggest="suggest" queryType="objectComplex" queryParams="object_address;nIDStatus">
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td>
				<fieldset id="by_doc" onclick="focusBocus(this);" style="filter:alpha(opacity=30);">
				<legend>Търсене по непогасен документ за продажаба</legend>
				<table class="input" style="margin-bottom:3px;">
					<tr class="even">
						<td>
							Номер
						</td>
						<td>
							<input type="text" name="doc_num" id="doc_num" style="width:230px;" onclick="clickDocNum();" >
						</td>
					</tr>
					<tr class="odd">
						<td>
							ЕИН на клиент
						</td>
						<td>
							<input type="text" name="doc_client_ein" id="doc_client_ein" style="width:230px;" suggest="suggest" queryType="client" queryParams="client_ein">
						</td>
					</tr>
					<tr class="even">
						<td>
							Име на клиент
						</td>
						<td>
							<input type="text" name="doc_client_name" id="doc_client_name" style="width:230px;" suggest="suggest" queryType="client" queryParams="client_name">
						</td>
					</tr>
				</table>
				</fieldset>
				<span style="font-weight:normal;vertical-align:4px;width:65px;">до&nbsp;месец:</span>
				<img src="images/mleft.gif" onclick="nextMonth('prev');" style="cursor:pointer;">
				<input 
					style="width:50px;" 
					id="dateM" 
					name="dateM" 
					type="text" 
					class="clear"  
					maxlength="7" 
					readonly 
					title="ММ.ГГГГ" 
					value={$smarty.now|date_format:'%m.%Y'}
				/>
				<img src="images/mright.gif" onclick="nextMonth('next');" style="cursor:pointer;">
				<span style="width:40px;">&nbsp;</span>
				<button id="button_search" onclick="formSubmit();">
					<img id="search_image" src="images/confirm.gif">Търси
				</button>
			</td>
		</tr>
	</table>
	<hr style="height:2px;">
	<table class="input" border="0">
		<tr>
			<td>
				<div id="result" rpc_resize="off" rpc_excel_panel="off" rpc_paging="off" style="height:330px;overflow:auto;"></div>
			</td>
		</tr>
	</table>
	<table class="input">
		<tr>
			<td>
				<div id="buttons">
					<button class="search" onclick="makeReceipt();">Квитанция [д]</button>&nbsp;
					<button class="search" onclick="makeInvoice2();">Фактура</button>&nbsp;
					<button style="color: red;" onclick="makeReceipt2();">Квитанция [o]</button>&nbsp;
					
				</div>	
			</td>
			<td style="background-color: #ffffcc; text-valign: center; padding-left: 10px; width: 190px;" >
				Клиента предпочита да плати с: 
			</td>
			
			<td>
				<input type="text" name="pay" id="pay" class="clear" style="text-align: left; font-weight: bold; width: 200px;" >
			</td>
						
			<td style="background-color:#ccffcc;width:100px;padding-left:10px;">
				ОБЩА СУМА:
			</td>
			<td style="background-color:#eeeeee;width:100px;">
				<input type="text" name="sum_all" id="sum_all" class="clear" style="text-align:right;font-weight:bold;width:100px;" readonly>
			</td>
		</tr>
	</table>

</form>

<script>
	onInit();
</script>