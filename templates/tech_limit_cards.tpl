<script>
{literal}
	rpc_debug = true;
	
	function editLimitCard(id) {
		var nID = id.split(',');
		dialogLimitCard(nID[0]);
	}

	function editRequest(id) {
		var nID = id.split(',');

		dialogTechRequest(nID[1]);
	}

	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'obj' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
			}
		}
	}
		
	function onSuggestObject(aParams) {
		$('nObject').value = aParams.KEY;
	}

	function formChange() {
		document.getElementById('sAct').value = 'load';
		loadXMLDoc2('result');
	}
	
	function formSearch() {
		document.getElementById('sAct').value = 'search';
		loadXMLDoc2('result');
	}
	
	function objChange() {
		$('nObject').value = 0;
		$('obj').value = '';
	}
{/literal}
</script>

<dlcalendar click_element_id="img_date_from" input_element_id="date_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_to" input_element_id="date_to" tool_tip="Изберете дата"></dlcalendar>

<div>
<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="sAct" name="sAct" value="load" />
	<input type="hidden" id="nObject" name="nObject" value="0" />
	
	<div class="page_caption">Лимитни КАРТИ</div>

	<table class = "page_data" style="width:100%">
		<tr>
			<td><br></td>
			<!-- <td class="buttons"> <button onclick="editLimitCard(0)"><img src="images/plus.gif"> Добави </button> </td> -->
		</tr>
		<tr>
			<td colspan="2" align="center">
				<table class="input" style="text-align:middle;" align="center">
				
					<tr>
						<td align="right">от дата:&nbsp;</td>
						<td colspan="2">
							<input type="text" id="date_from" name="date_from" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_from" />
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="vertical-align: top;">до дата:</span>&nbsp;
							<input type="text" id="date_to" name="date_to" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_to" />
						</td>
						<td align="left">статус: &nbsp;
							<select name="sStatus" id="sStatus" style="width: 150px;" >
								<option value="0">Всички</option>
								<option value="active" selected>Активни</option>
								<option value="closed">Приключени</option>
								<option value="cancel">Анулирани</option>
							</select>
						</td>
						<td>&nbsp;</td>						
					</tr>
					
					<tr>
						<td style="width: 110px;" align="right">фирма:&nbsp;</td>
						<td style="width: 220px;">
							<select name="nIDFirm" id="nIDFirm" onChange="formChange();" style="width: 200px;" >
							</select>
						</td>
						<td align="right" style="width: 80px;">регион:&nbsp;</td>
						<td style="width: 220px;"> 
							<select name="nIDOffice" id="nIDOffice" style="width: 200px;" ></select>
						</td>
						<td>&nbsp;</td>
					</tr>

					<tr>
						<td align="right">тип обслужване:&nbsp;</td>
						<td>
							<select name="sTypeBugnq" id="sTypeBugnq" style="width: 200px;" >
								<option value="0">---Всички---</option>
								<option value="create">Изграждане</option>
								<option value="destroy">Снемане</option>
								<option value="holdup">Профилактика</option>
								<option value="arrange">Аранжиране</option>
							</select>
						</td>
						<td align="right">обект:&nbsp;</td>
						<td>
							<input type="text" name="obj" id="obj" style="width: 200px;" suggest="suggest" queryType="obj" queryParams="nIDOffice" onChange="objChange();" />
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					  <td align="right" style="width: 200px">Номер:&nbsp;
					    <input type="text" name="nNumber" id="nNumber" style="width: 50px; text-align:right;" onkeypress="return formatDigits(event);" />
					  </td>
					  <td>
						<button type="button" name="Button" class="search" onClick="formSearch();"><img src="images/confirm.gif">Търси</button>
					  </td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<hr />

<div id="result" rpc_resize="no" style="overflow: auto;"></div>

</form>
</div>

<script>
	loadXMLDoc2('result');
</script>