{literal}
	<script>
		rpc_debug = true;
		rpc_method = 'POST';
			
		InitSuggestForm = function() {
			for ( var i = 0; i < suggest_elements.length; i++ ) {
				switch( suggest_elements[i]['id'] ) {
					case 'nObjectNum':
					case 'sObjectName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
					break;
				}
			}
		}
		
		function onSuggestObject( aParams ) {
			var aStuff = aParams.KEY.split(';');
			
			$('nIDObject').value 	= aStuff[0];
			$('nObjectNum').value 	= aStuff[1];
			$('sObjectName').value 	= aStuff[2];
		}
		
		function onChangeObjectNum() {
			$('nIDObject').value 	= 0;
			$('sObjectName').value 	= "";
		}
		
		function onChangeObject() {
			$('nIDObject').value 	= 0;
			$('nObjectNum').value 	= "";
		}
		
		function onInit() {
			loadXMLDoc2('load');

			rpc_on_exit = function() {
				var firm 	= $('firm').value;
				var firm2	= $('nIDFirm').value;

				if ( firm2 == 0 ) {
					//document.getElementById('nIDFirm').selectedIndex = firm;
					$('nIDFirm').value = $('firm').value;
					
					loadoffices();

					rpc_on_exit = function() {
						var office	= $('office').value;
						var office2	= $('nIDOffice').value;
										
						if ( office2 == 0 ) {
							$('nIDOffice').value = office;
						}

						rpc_on_exit = function() {};
					};
				}
			};
		}
	
		function openOrder(id) {
			if (id) {
				dialogOrder( 'id=' + id );
			}
		}
		
		function getResult() {			
			$('nRefreshTotals').value = 1;
			
			loadXMLDoc2('result');
			
			return true;
		}

		function loadoffices() {						
			$('firm').value = $('nIDFirm').value;
			
			loadXMLDoc2('loadOffices');
		}

		function onPrint(type) {
			loadDirect(type);
		}	

		function isEmpty(str) {
			if ( (str.length == 0) || (str == null) ) {
				return true;
			} else { 
				return false; 
			}
		}	

		function isset() {
		    var a = arguments, l = a.length, i = 0;
		    
		    if ( l === 0 ) {
		        //throw new Error('Празно'); 
		    	return false; 
		    }
		    
		    while ( i !== l ) {
		        if ( typeof(a[i]) == 'undefined' || a[i] === null) { 
		            return false; 
		        } else { 
		            i++; 
		        }
		    }
		    return true;
		}		
				
		function getDetail(sData) {
			var aData 		= new Array();
			var nID			= 0;
			var sParams		= '';
			
			var nIDFirm		= $('nIDFirm').value;
			var nIDOffice	= $('nIDOffice').value;
			var nIDObject	= $('nIDObject').value;
			var sFromDate	= $('sFromDate').value;
			var sToDate		= $('sToDate').value;
			var sMonth		= $('sMonth').value;
			
			if ( !isEmpty(sData) ) {
				aData = sData.split('***');
			}

			if ( isset(aData[0]) ) {
				nID 	= aData[0];

				sParams	= 'nIDNomenclature='+nID+'&nIDFirm='+nIDFirm+'&nIDOffice='+nIDOffice+'&nIDObject='+nIDObject+'&sFromDate='+sFromDate+'&sToDate='+sToDate+'&sMonth='+sMonth;
				//dialogMoneyNomenclaturesOverview(nIDFirm, dFrom, dTo);
				
				dialogMoneyNomenclaturesDetails(sParams);
//				switch( nID ) {
//					case '-3':	// Неизвестни
//						alert(2);
//					break;
//	
//					case '-1':	// ДДС
//						alert(1);
//					break;
//	
//					default:	// Номенклатури
//						alert(nID);
//					break;												
//				}				
			} else {
				alert('no');
			}
		}			
	
	</script>
	
	<style>
		table.total
		{
			font-size: 12px;
			margin: 0px;
			padding: 0px;
			border: 1px solid black;
			border-spacing: 0px;
			border-collapse: separate;
			background-color: D2DCF0;
			width: 330px;
		}
		input.total
		{
			border: 1px solid black;
			font-size: 12px;
			background-color: DCE6F0;
			width: 200px;
			height: 20px;
			text-align: right;
		}
	</style>
{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
		<input type="hidden" id="firm" name="firm" value="{$nIDFirm}" />
		<input type="hidden" id="office" name="office" value="{$nIDOffice}" />
		<input type="hidden" id="dFrom" name="dFrom" value="{$dFrom}" />
		<input type="hidden" id="dTo" name="dTo" value="{$dTo}" />
		<input type="hidden" id="nRefreshTotals" name="nRefreshTotals" value="0" />
		
		<div class="page_caption">Парични Потоци – Обобщена</div>

		<table class="input">
			<tr>
				<td colSpan=4 height=3><SPACER height="1" type="block"></td>
			</tr>
			<tr>
				<td>
					<div style="width: 900px; margin-left: 10px;">
						<table class="input" border="0">
							<tr>
								<td align="left">Фирма:&nbsp;</td>
								<td align="left">
									<select class="select300" name="nIDFirm" id="nIDFirm" onchange="loadoffices();" />
								</td>
								
								<td>&nbsp;</td>
								
								<td align="left">Регион:&nbsp;</td>
								<td align="left">
									<select class="select300" name="nIDOffice" id="nIDOffice" />
								</td>
								
								<td>&nbsp;</td>
							</tr>
							
							<tr>
								<td align="left">Обект:&nbsp;</td>
								<td align="left">
									<input type="text" id="nObjectNum" name="nObjectNum" style="width: 60px;" suggest="suggest" queryType="objByNum" onkeypress="formatDigits( event );" onchange="onChangeObjectNum();" maxlength="12" />&nbsp;
									<input type="text" id="sObjectName" name="sObjectName" style="width: 240px;" suggest="suggest" queryType="objByName" onchange="onChangeObject();" />
								</td>
								
								<td>&nbsp;</td>
								
								<td align="left">Към месец:&nbsp;</td>
								<td>
									<select name="sMonth" id="sMonth" class="select100" />
								</td>
								
								<td>&nbsp;</td>
							</tr>
							
							<tr>
								<td align="left"><b>Период : </b>&nbsp;</td>
								<td align="left">
									От:&nbsp;
									<input type="text" name="sFromDate" id="sFromDate" class="inp100" onkeypress="return formatDate( event, '.' );" />
									&nbsp;
									<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editFromDate" />
									
									&nbsp;&nbsp;
									
									До:
									<input type="text" name="sToDate" id="sToDate" class="inp100" onkeypress="return formatDate( event, '.' );" />
									&nbsp;
									<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editToDate" />
								</td>
								
								<td colspan="3">&nbsp;</td>
								
								<td align="left">
									<button type="button" name="Button" class="search" onClick="getResult();"><i class="far fa-check fa-lg"></i>Търси</button>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
		
		<div style="margin-left: 15px;">
			<table border="0">
				<tr>
					<td colSpan=4 height=3><SPACER height="1" type="block"></td>
				</tr>
				<tr>
					<td>
						<table class="total">
							<tr>
								<td>Общо разходи:</td>
								<td align="right">
									<input type="text" class="total" id="nTotalExpense" name="nTotalExpense" readonly />
								</td>
							</tr>
						</table>
					</td>
					
					<td>&nbsp;</td>
					
					<td>
						<table class="total">
							<tr>
								<td>Общо приходи:</td>
								<td align="right">
									<input type="text" class="total" id="nTotalEarning" name="nTotalEarning" readonly />
								</td>
							</tr>
						</table>
					</td>
					
					<td>&nbsp;</td>
					
					<td>
						<table class="total">
							<tr>
								<td>Промяна:</td>
								<td align="right">
									<input type="text" class="total" id="nTotalChange" name="nTotalChange" readonly />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class="total">
							<tr>
								<td>Начална наличност:</td>
								<td align="right">
									<input type="text" class="total" id="nTotalStartBalance" name="nTotalStartBalance" readonly />
								</td>
							</tr>
						</table>
					</td>
					
					<td>&nbsp;</td>
					
					<td>
						<table class="total">
							<tr>
								<td>Крайна наличност:</td>
								<td align="right">
									<input type="text" class="total" id="nTotalEndBalance" name="nTotalEndBalance" readonly />
								</td>
							</tr>
						</table>
					</td>
					
					<td colspan="2">&nbsp;</td>
				</tr>
			</table>
		</div>
		
		<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="overflow: auto; height: 365px;"></div>
		
		
	
		<div id="search" style="padding-top: 10px;"><hr>
			<table width="100%" cellspacing="1px">
				<tr valign="top">
					<td style="width: 100%">&nbsp;</td>
					
					<td valign="bottom" align="right" style="width: 180px;">
						<a href="#" onclick="onPrint('export_to_xls');"><img src="images/excel.gif" border="0" /></a>&nbsp;&nbsp;
					</td>
					
					<td valign="bottom" align="center" style="width: 180px;">
						<a href="#" onclick="onPrint('export_to_pdf');"><img src="images/pdf2.gif" border="0" /></a>&nbsp;&nbsp;
					</td>
							
					<td valign="top" align="right" style="width: 150px;">
						<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
					</td>
				</tr>
			</table>
		</div>
		
	</form>
</div>

<script>
	onInit();
</script>