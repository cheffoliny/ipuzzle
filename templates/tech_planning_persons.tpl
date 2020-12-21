{literal}
	<script>
		rpc_debug = true;
		rpc_xsl = "xsl/tech_planning_schedule.xsl";
			
		function onInit() {
			$('pMonth').style.display = "none";
			loadXMLDoc2('load');
		}
		
		function getResult() {
			if( $('nIDOffice').value != 0 ) {
				$('start').value = '0';
				$('end').value = '0';
				loadXMLDoc2('result');
			}
		}
		
		function nextDate(act) {
			var oldDate = $('date').value;
			var oldDD = oldDate.substr(0,2);
			var oldMM = oldDate.substr(3,2);
			var oldYY = oldDate.substr(6,4);
			
			var newDate = new Date();
			newDate.setFullYear(oldYY,oldMM-1,oldDD);
			
			if(act == 'next')
				newDate.setDate(newDate.getDate() + 1);
			else
				newDate.setDate(newDate.getDate() - 1);
			
			var newDD = newDate.getDate();
			var newMM = newDate.getMonth()+1;
			var newYY = newDate.getYear();
			
			if( newDD < 10 ) newDD = "0" + newDD;
			if( newMM < 10 ) newMM = "0" + newMM;
			
			var sDate = newDD+'.'+newMM+'.'+newYY;		
			$('date').value = sDate;
			getResult();
		
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
			getResult();			
		}
		
		function openPerson(id) {
			dialogPerson(id);
		}
		function openPersonalCard(id) {
			dialogPersonalCard(id);
		}
		
		function openLimitCard( nIDLimitCard ) {
			if( parseInt( nIDLimitCard ) )
				dialogLimitCard(nIDLimitCard, nIDLimitCard);
		}
		
		function changeType(type) {
			if( type == 'day') {
				$('pDay').style.display = "block";
				$('pMonth').style.display = "none";
			} else {
				$('pDay').style.display = "none";
				$('pMonth').style.display = "block";
			}
		}
		
		
		rpc_on_exit = function ( nCode ) {
			if( !parseInt( nCode ) ) {
				document.getElementById('OnlyTecnicks').focus();
				if( $('start').value != '0' ) {
					$('start').value = '0';
					$('end').value = '0';
					if($('id_request_from_contract').value == 0) {
						var id_office = parent.document.getElementById('id_request_office').value;
						parent.document.getElementById('tech_plannig_requests').src = 'page.php?page=tech_planning_requests&id_office='+id_office;
					}
				}
			}
		}
		
	</script>
	
	<style>
		button.saveplan {
			color:#ffffff; 
			background:#612c2c; 
			font:16px verdana;
			
	
		}
		table.result td.person_on_duty a:link,
		table.result td.person_on_duty a:visited {
			color:green;
			text-decoration: none;
			font-weight : bold;
		}
		table.result td.person_on_leave a:link,
		table.result td.person_on_duty a:visited {
			color:red;
			text-decoration: none;
			font-weight : bold;
		}
	</style>
{/literal}

<dlcalendar click_element_id="imgDate" input_element_id="date" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="start" id="start" value="0">
	<input type="hidden" name="end" id="end" value="0">
	<input type="hidden" name="id_request" id="id_request" value="0">
	<input type="hidden" name="id_request_from_contract" id="id_request_from_contract" value="{$nIDRequest|default:0}">
	
	<table class="page_data">
		<tr style="height:35px;">
			<td width="200px" class="page_caption">График</td>
			<td align="right" valign="middle" width="100px">Фирма</td>
			<td valign="middle" width="170px">
				<select style="width:150px;" class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
			</td>
			<td align="right" valign="middle" width="50px">
				<input type="checkbox" class="clear" checked="checked" name="OnlyTecnicks" id="OnlyTecnicks" onClick = "getResult();">
			</td>
			<td valign="middle" width="150px">
				само техници
			</td>
			
			<td>
				<div id="pDay">
					<table>
						<tr>
							<td valign="middle" width="15px">
								<button onclick="nextDate('prev');" style="width:10px"><img src="images/mleft.gif" /></button>
							</td>
							<td valign="middle" align="center" width="75px">
								<input style="width:70px;" id="date" name="date" type="text" class="clear" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" />
							</td>
							<td valign="middle" width="15px">
								<button onclick="nextDate('next');" style="width:10px"><img src="images/mright.gif" /></button>
							</td>
							<td>
								<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgDate" />
							</td>
						</tr>
					</table>
				</div>
			</td>
			
			<td>
				<div id="pMonth">
					<table>
						<tr>
							<td valign="middle" width="15px">
								<button onclick="nextMonth('prev');" style="width:10px"><img src="images/mleft.gif" /></button>
							</td>
							<td valign="middle" align="center" width="75px">
								<input style="width:70px;" id="dateM" name="dateM" type="text" class="clear" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" />
							</td>
							<td valign="middle" width="15px">
								<button onclick="nextMonth('next');" style="width:10px"><img src="images/mright.gif" /></button>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td width="30%">
				&nbsp;
			</td>
		</tr>
		
		<tr style="height:30px;">
			<td align="center">
				Тип:
				<select style="width:100px;" name="type" id="type" onchange="changeType(this.value)">
					<option value="day">Дневен</option>
					<option value="month">Месечен</option>
				</select>
			</td>
			<td align="right" valign="middle" width="100px">Регион</td>
			<td valign="middle" width="170px">
				<select style="width:150px;" class="default" name="nIDOffice" id="nIDOffice" onchange="getResult();" />
			</td>
			<td align="right" valign="middle" width="50px">
				<input type="checkbox" class="clear" checked="checked" name="closedLimitCards" id="closedLimitCards" onClick = "getResult();">
			</td>
			<td valign="middle" width="150px">
				затворени карти
			</td>
			<td colspan="4" align="center" width="105px">
				<button type="button" onClick="getResult();" class="search"><img src="images/reload.gif" />Обнови</button>
			</td>
		<!--	<td colspan="3" width="200px" align="center">
				<button class="saveplan" type="button" name="button" onClick="save();" ><b>Запази</b></button>
			</td> 
		
			<td colspan="4" width="30%">
				&nbsp;
			</td>
			-->
		</tr>
	</table>
	
	<hr>
	
	<div id="result" rpc_excel_panel="off"></div>

</form>

<script>
	onInit();
</script>