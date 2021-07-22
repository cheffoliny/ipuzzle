{literal}
	<script>
		rpc_debug = true;
		//var my_action = '';
				
		function roundNumber(num, dec) {
			var result = Math.round( num * Math.pow( 10, dec ) ) / Math.pow( 10, dec );
			
			return result;
		}
			
		InitSuggestForm = function() {			
			for(var i = 0; i < suggest_elements.length; i++) {
				if( suggest_elements[i]['id'] == 'firm' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestFirm );
				}
				
				if( suggest_elements[i]['id'] == 'region' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestRegion );
				}
				
				if( suggest_elements[i]['id'] == 'obj' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestObj );
				}
				
				if( suggest_elements[i]['id'] == 'region_object' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
				}				

				if( suggest_elements[i]['id'] == 'position' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestPosition );
				}
			}
		}
			
		function onSuggestFirm ( aParams ) {
			$('id_firm').value = aParams.KEY;
			$('region').value = '';
			$('nIDOffice').value = 0;
			$('obj').value = '';
			$('id_object').value = 0;
		}
		
		function sleep(milliseconds) {
			var start = new Date().getTime();
			
			for (var i = 0; i < 1e7; i++) {
				if ((new Date().getTime() - start) > milliseconds) {
					break;
				}
			}
		}		
		
		function onSuggestRegion ( aParams ) {
			$('nIDOffice').value = aParams.KEY;
			$('obj').value = '';
			$('id_object').value = 0;
		}
		
		function onSuggestObj ( aParams ) {
			$('id_object').value = aParams.KEY;
		}
		
		function onSuggestObject ( aParams ) {
			$('id_object2').value = aParams.KEY;
		}		

		function onSuggestPosition ( aParams ) {
			$('id_position').value = aParams.KEY;
		}

		function onFirmChange() {
			$('id_firm').value = 0;
			$('nIDOffice').value = 0;
			$('id_object').value = 0;
			$('region').value = '';
			$('obj').value = '';
		}

		function onRegionChange() {
			$('nIDOffice').value = 0;
			$('id_object').value = 0;
			$('obj').value = '';
		}
		
		function onRegionObjectChange() {
			$('id_object').value = 0;
		}
		
		function onRegionObjChange() {
			$('id_object2').value = 0;
		}		

		function onPositionChange() {
			$('id_position').value = 0;
		}

		function submit_form()
		{
//			var status 	= $('status').value;
//			var days	= $('countDays').value;
//			
//			if ( (status == 'moved') || (status == 'vacate') ) {
//				if ( days > 0 ) {
//					if ( confirm('Желаете ли да добавите наработка за изплащане на отпуск?') ) {
//						showDiv();
//								
//						rpc_on_exit = function() {
//							loadXMLDoc('save', 0);
//							
//							rpc_on_exit = function() {
//								window.location.reload(true);
//							};						
//						};
//					} else {
//						loadXMLDoc('save', 0);
//						
//						rpc_on_exit = function() {
//							window.location.reload(true);
//						};				
//					}
//				} else {
//					loadXMLDoc('save', 0);
//					
//					rpc_on_exit = function() {
//						window.location.reload(true);
//					};
//				}
//										
//			} else {
//				loadXMLDoc('save', 0);
//				
//				rpc_on_exit = function() {
//					window.location.reload(true);
//				};				
//			}
			
			loadXMLDoc( "save", 0 );
			
			rpc_on_exit = function()
			{
				window.location.reload( true );
			}
		}

		function setSalaries() {
			var nIDPerson 	= $('id').value;
			var year 		= $('year').value;
			var month 		= $('month').value;
			var firm 		= $('nIDFirm').value;
			var office 		= $('nIDOffice').value;
								
			//sleep(100);
								
			var handler = dialogNewSalary( 0, nIDPerson, month, year, 1, 1, firm, office, 'ДОМ' );	
			handler.focus();			
		}
		
		function formChange(type) {
			if ( type == 'firm' ) {
				document.getElementById('nIDOffice').value = 0;
			}
			loadXMLDoc('result');
		}	

		function update_character() {
			var id = document.getElementById('id').value;
			if ( id == 0 ) {
				alert('Служителя все още не е създаден!');
			} else {
				dialogUploadCharacter( id );
			}
		}
		
		function hideDiv() {
			var hideDiv		= $("ShowHide");
			var blokDiv		= $("blokaj");

			if ( form = document.getElementById('form1') ) {
				for ( i = 0; i < form.elements.length - 1; i++ ) {
					form.elements[i].setAttribute('disabled', '');					
				}
			}	
						
			blokDiv.style.display 	= "none";
			hideDiv.style.display 	= "none";
		}	
		
		function showDiv() {
			var hideDiv		= $("ShowHide");
			var blokDiv		= $("blokaj");
			
			if ( form = document.getElementById('form1') ) {
				for ( i = 0; i < form.elements.length - 1; i++ ) {
					form.elements[i].setAttribute('disabled', 'disabled');					
				}
			}	
			
			blokDiv.style.backgroundColor = "#999999";	
			blokDiv.style.zIndex	= 30;
			blokDiv.style.opacity 	= 0.5;
			blokDiv.style.filter 	= "alpha(opacity=50)";	
			blokDiv.style.display 	= "block";
			
			hideDiv.style.zIndex 	= 40;
			hideDiv.style.backgroundColor = "#ffffff";
			hideDiv.style.display 	= "block";
		}
		
		function codeChange(obj) {
			var decr = document.getElementById('description');
			var name = obj[obj.selectedIndex].id;
			decr.value = name;
		}	
			
		function sum_one(sum) {
			var mult = $('count').value;
			
			if ( mult < 1 ) {
				mult = 1;
			}
			
			$('sum_total').value = roundNumber( (sum * mult), 2 );
			
		}
	
		function sum_two(mult) {
			var sum 		= $('sum').value;
			var sum_total 	= $('sum_total').value;
			
			if( (sum > 0) && (mult > 0) ) {
				$('sum_total').value = roundNumber( (sum * mult), 2 );
			} else if ( (sum_total > 0) && (mult > 0) ) {
				$('sum').value 		 = roundNumber( (sum_total / multi), 2 );
			}
		}
		
		function sum_three(sum) {
			var mult = $('count').value;
			
			if ( mult < 1 ) {
				mult = 1;
			}
			
			$('sum').value = roundNumber( (sum / mult), 2 );
		}			
		
		function salSubmit() {
			$('nCodeSalary').value 		= $('code').value;
			$('nIDFirmSalary').value 	= $('nIDFirm2').value;
			$('nIDOfficeSalary').value 	= $('nIDOffice2').value;
			$('sDescription').value 	= $('description').value;
			$('nSumSalary').value 		= $('sum').value;
			$('nCountSalary').value 	= $('count').value;
			$('nTotalSumSalary').value 	= $('sum_total').value;
			
			loadXMLDoc('salary');
			
		}
			
	</script>
{/literal}

<dlcalendar click_element_id="img_date_in" input_element_id="date_in" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_out" input_element_id="date_out" tool_tip="Изберете дата"></dlcalendar>

<div id="blokaj" style="width: 100%; height: 100%; display: none; position: absolute;" ></div>


<div id="mainDiv" name="mainDiv">
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="id" name="id" value="{$id|default:0}" />
		<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
		<input type="hidden" id="idc" name="idc" value="0" />
		<input type="hidden" id="id_object" name="id_object" value="0" />
		<input type="hidden" id="id_object2" name="id_object2" value="0" />
		<input type="hidden" id="id_position" name="id_position" value="0" />
		<input type="hidden" id="countDays" name="countDays" value="0" />
		<input type="hidden" id="year" name="year" value="{$year}" />
		<input type="hidden" id="month" name="month" value="{$month}" />
		
		<input type="hidden" id="nCodeSalary" name="nCodeSalary" value="0" />
		<input type="hidden" id="nIDFirmSalary" name="nIDFirmSalary" value="0" />
		<input type="hidden" id="nIDOfficeSalary" name="nIDOfficeSalary" value="0" />
		<input type="hidden" id="sDescription" name="sDescription" value="" />
		<input type="hidden" id="nSumSalary" name="nSumSalary" value="0" />
		<input type="hidden" id="nCountSalary" name="nCountSalary" value="0" />
		<input type="hidden" id="nTotalSumSalary" name="nTotalSumSalary" value="0" />


		{include file='person_tabs.tpl'}

		<div class="container-fluid mb-4">
				<!-- начало на работната част -->

			<div class="row clearfix mt-2">
				<div class="col-4 pl-3">
					<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Служителя е зачислен към </div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend">
							<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Фирма..."></span>
						</div>
						<select class="form-control" name="nIDFirm" id="nIDFirm" onChange="formChange('firm');" ></select>
					</div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend">
							<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Регион..."></span>
						</div>
						<select class="form-control" name="nIDOffice" id="nIDOffice" ></select>
					</div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend">
							<span class="fa fa-mailbox fa-fw" data-fa-transform="right-22 down-10" title="Име..."></span>
						</div>
						<input class="form-control" name="obj" type="text" id="obj" suggest="suggest" queryType="obj" queryParams="id_firm;nIDOffice" onchange="onRegionObjectChange()" onpast="onRegionObjectChange()" />
					</div>
				</div>
				<div class="col-4">
					<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Длъжност </div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend">
							<span class="far fa-badge-check fa-fw" data-fa-transform="right-22 down-10" title="Семейно положение..."></span>
						</div>
						<select class="form-control" name="nPositionNKID" id="nPositionNKID" ></select>
					</div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend">
							<span class="far fa-badge-check fa-fw" data-fa-transform="right-22 down-10" title="IBAN..."></span>
						</div>
						<input class="form-control" name="position" id="position" type="text" suggest="suggest" queryType="position" queryParams="id_position" onchange="onPositionChange()" onpast="onPositionChange()" />
					</div>
				</div>
				<div class="col">
					<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Служебни данни </div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend" id="img_date_in">
							<span class="fa fa-calendar-check fa-fw" data-fa-transform="right-22 down-10" title="Дата на постъпване"></span>
						</div>
						<input class="form-control" type="text" name="date_in" id="date_in" onkeypress="return formatDate(event, '.');" maxlength="10" title="Дата на постъпване [ДД.ММ.ГГГГ]" />
					</div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend" id="img_date_out">
							<span class="fa fa-calendar-times fa-fw" data-fa-transform="right-22 down-10" title="Дата на напускане"></span>
						</div>
						<input class="form-control" type="text" name="date_out" id="date_out" onkeypress="return formatDate(event, '.');" maxlength="10" title="Дата на напускане [ДД.ММ.ГГГГ]" />
						<input class="form-control bg-dark ml-1 pr-0" name="length_service" type="text" id="length_service" maxlength="3" onkeypress="return formatNumber(event);" title="Прослужено време..." readonly/>
					</div>
					<div class="input-group input-group-sm mb-1">
						<div class="input-group-prepend">
							<span class="fa fa-toggle-off fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
						</div>
						<select class="form-control" id="status" name="status" >
							<option value="active">активен</option>
							<option value="vacate">напуснал</option>
							<option value="moved">прехвърляне</option>
						</select>
					</div>
				</div>

			</div>

			<div class="row">
				<div class="alert alert-danger alert-dismissable transparent-half col p-4 m-3">
					Забележка: не е възможно напускането, ако има налични удръжки!
				</div>
			</div>

			<div class="row mt-2">
				<div class="col">
					<div class="input-group input-group-sm mb-1 text-white bg-dark p-2">История на назначенията</div>
					<div class="w-100" id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height: 160px; overflow: auto;"></div>
				</div>
			</div>
		</div>
		<nav class="navbar fixed-bottom flex-row py-2 navbar-expand-lg p-2" id="search">
			<div class="col text-right p-2">

					<button class="btn btn-sm btn-success mr-1"	onClick="return submit_form();" ><i class="fas fa-check" ></i> Запиши </button>
					<button class="btn btn-sm btn-danger"	    onClick="window.close();"		><i class="far fa-window-close" ></i> Затвори </button>

			</div>
		</nav>
	</form>
</div>

<div class="conjtent" style="position: absolute; top: 60px; left: 300px; width: 400px; heigh: 400px; display: none; border: 1px solid #000000; padding: 6px;" id="ShowHide">
	<form action="" method="POST" name="form2" onsubmit="return false;" style="margin: 0px;">

		<div class="page_caption">Наработка на служител за {$month}.{$year}</div>
		<fieldset style="height: 200px;">
			<legend>Обща информация за наработката:</legend><br />
			<table class="input" >
			
				<tr class="even">
					<td width="140">Код на наработката:</td>
					<td width="280px">
						<table class="input" cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<select id="code" name="code" style="width: 120px;" onChange="codeChange(this);"></select>
								</td>
								<td align="right">
									<input type="checkbox" id="auto" name="auto" class="clear" disabled />
								</td align="right">
								<td>Автоматична</td>
							</tr>
						</table>
					</td>
				</tr>
				
				<tr class="even">
					<td>Фирма:</td>
					<td>
						<select name="nIDFirm2" id="nIDFirm2" style="width: 260px;" onChange="formChange();" ></select>
					</td>
				</tr>

				<tr class="even">
					<td>Регион:</td>
					<td>
						<select name="nIDOffice2" id="nIDOffice2" style="width: 260px;" onChange="formChange();" ></select>
					</td>
				</tr>
				
				<tr class="even">
					<td>Към обект:</td>
					<td><input id="region_object" name="region_object" type="text" class="default" suggest="suggest" queryType="region_object" queryParams="nIDFirm2;nIDOffice2" onchange="onRegionObjChange()" onpast="onRegionObjChange()" style="width: 260px;" /></td>
				</tr>
				
				<tr class="even">
					<td>Кратко описание:</td>
					<td><input id="description" name="description" type="text" class="default" style="width: 260px;" /></td>
				</tr>
				
			</table><br />
		</fieldset>
		
		<fieldset style="height: 150px;">
			<legend>Стойност на наработката:</legend><br />
			<table class="input">
			
				<tr class="even">
					<td width="140">Ед. стойност:</td>
					<td><input id="sum" name="sum" type="text" class="default" onKeyPress="return formatMoney(event);" onKeyUp="sum_one(this.value);" /></td>
				</tr>
				
				<tr class="even">
					<td>Количество:</td>
					<td><input id="count" name="count" type="text" class="default" onkeypress="return formatNumber(event);" onKeyUp="sum_two(this.value);" value="1" /></td>
				</tr>
				
				<tr class="even">
					<td>Обща стойност:</td>
					<td><input id="sum_total" name="sum_total" type="text" class="default" onKeyPress="return formatMoney( event );" onKeyUp="sum_three(this.value);" /></td>
				</tr>
				
			</table><br />
		</fieldset>
		
		<table class="input" style="height: 30px;">
			<tr class="odd">
				<td width="200">&nbsp;</td>
				<td><button type="button" class="search" onClick="salSubmit();"> Запиши </button></td>
				<td style="text-align:right;">
					
					<button onClick="hideDiv();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
</div>


<script>
	loadXMLDoc('result');
	{if !$personnel_edit}
		
		if( form=document.getElementById('form1') )  
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
	{/if}	
</script>
