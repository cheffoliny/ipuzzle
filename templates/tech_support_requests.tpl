{literal}
<script>
	rpc_debug = true;

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

	function onInit() {
		$('panel2').style.display = "none";
		loadXMLDoc2('load');
	}
	
	function result() {
		loadXMLDoc2('result');
		$('result').style.display = "block";
	}
	
	function editRequest(id) {
		if ( id.length > 1 ) {
			var ids = id.split(',');
			dialogTechRequest(ids[0]);
		} else dialogTechRequest(id);
	}

	function viewLimitCard(id) {
		var ids = id.split(',');
		dialogLimitCard(ids[1]);
	}
	
	function viewObject( id )
	{
		var ids = id.split( ',' );
		dialogObjectSupport( "nID=" + ids[2] );
	}
	
	function objChange() {
		$('nObject').value = 0;
		$('obj').value = '';
	}
	
	function checkAll( bChecked ) {
		var aCheckboxes = document.getElementsByTagName('input');
		
		for( var i=0; i<aCheckboxes.length; i++ )
		{
			if( aCheckboxes[i].type.toLowerCase() == 'checkbox' )
			{
				if( aCheckboxes[i].id != 'nNoLimitCard' && aCheckboxes[i].id != 'nActiveLC' )
				{
					aCheckboxes[i].checked = bChecked;
				}
			}
		}
	}
	
	function just_do_it() {
		switch (getById('sel').value) {
			case '1':
				checkAll( true );
				break;
			case '2':
				checkAll( false );
				break;
			case '3':
				loadXMLDoc2('limit', 1);
				break;
			case '4':
				if ( confirm('Наистина ли желаете да анулирате избраните Задачи?') ) {
					loadXMLDoc2('delete', 1);
				}
				break;
		}
	}

	function openPerson( id ) {
		var spl = id.split(',');
		var nIDPerson = spl[1];
		dialogPerson( nIDPerson );
	}
	function openContractPDF( id ) {
		var spl = id.split(',');
		var contract_id = spl[0];
		$('id_contract').value = contract_id;
		loadDirect('export_to_pdf')
	}
	
	function ignoreContract( id ) {
		if ( confirm('Наистина ли желаете да откажете този договор?') ) {
			var spl = id.split(',');
			var contract_id = spl[0];
			$('id_contract').value = contract_id;
			loadXMLDoc('ignoreContract',1);
		}	
	}
	
	function openRequest( id ) {
		var spl = id.split(',');
		var contract_id = spl[0];
		dialogObjectToContract(contract_id);
	}
	
	function onClickRequests() {
		$('panel1').style.display = "block";
		$('panel2').style.display = "none";
		$('result').style.display = "none";
		try {
			$('sfield').value = "";
			$('stype').value = "";
		} catch(e) {
		}
		//$('stype').value = "";
	}
	function onClickContracts() {
		$('panel1').style.display = "none";
		$('panel2').style.display = "block";
		$('result').style.display = "none";
		try {
			$('sfield').value = "";
			$('stype').value = "";
		} catch(e) {
		}
		
		//$('sfield').value = "";
		//$('stype').value = "";
	}
	
	function openFilter(type) {
		var id;
		if(type == 1) {
			dialogTechSupportRequestsFilter(0);
		} else {
			id = $('schemes').value;
			if(id != 0) {
				dialogTechSupportRequestsFilter(id);
			}
		}
	}

	function deleteFilter(schemes)
	{
		if(schemes.value > 0)
			if( confirm('Наистина ли желаете да премахнeте филтърът?') )
			{
				loadXMLDoc('deleteFilter',6);
			}
	}
	
	function goToPlanning() {
		window.location = 'page.php?page=tech_planning';
	}
</script>
{/literal}

<dlcalendar click_element_id="img_date_from" input_element_id="date_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_to" input_element_id="date_to" tool_tip="Изберете дата"></dlcalendar>

<div>
<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nObject" name="nObject" value="0" />
	<input type="hidden" id="id_contract" name="id_contract" value = "0">
	
	<div class="page_caption">Задачи за техническо облужване</div>

		<table class="input">
			
			<tr>
				<td>
					<button onclick="goToPlanning();">Към планиране</button>
				</td>
				<td style="width:300px;">
					&nbsp;
				</td>
				<td align="right">
					Филтри:
				</td>
				<td style="width:200px;">
					<select name="schemes" id="schemes"></select>
				</td> 
				
				<td>
					<button style="width: 30px" id=b25 title="Нов филтър" name="Button5" onClick="openFilter(1);" ><img src="images/plus.gif" /></button>&nbsp;
					<button style="width: 30px" name="Button4" id=b25 title="Редактиране на филтър" onClick="openFilter(2);"><img src=images/edit.gif /></button>&nbsp;
					<button style="width: 30px" name="Button3" id=b25 title="Премахване на филтър" onClick="deleteFilter(schemes);"><img src=images/erase.gif /></button>
				</td>
				<td>
					&nbsp;
				</td>
				<td class="buttons" align="right">
					<button onclick="editRequest(0)"><i class="far fa-plus"></i> Нова Задача </button>
				</td>
			</tr>
		</table>
		<table class="input">
			<tr>
				<td align="right" style="width: 112px;">от дата:&nbsp;</td>
				<td style="width: 100px;">
					<input type="text" id="date_from" name="date_from" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_from" />
				</td>
				<td align="right" style="width: 50px;">до дата:</td>
				<td style="width: 110px;">
					<input type="text" id="date_to" name="date_to" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_to" />
				</td>
				<td align="right" style="width: 50px;">
					Задачи
				</td>
				<td style="width: 20px;">
					<input type="radio" class="clear" id="type" name="type" value="requests" checked="checked" onClick = "onClickRequests();" />
				</td>
				<td align="right" style="width: 80px;">
					Ел. договори
				</td>
				<td>
					<input type="radio" class="clear" id="type" name="type" value="contracts" onClick = "onClickContracts();"/>
				</td>
				<td class="buttons">&nbsp;</td>
			</tr>
		</table>
		
		<div id="panel1">
		
			<table class = "page_data" style="width:100%">

				<tr>
					<td style="width: 110px;" align="right">фирма:&nbsp;</td>
					<td style="width: 220px;">
						<select name="nIDFirm" id="nIDFirm" onChange="loadXMLDoc2('loadOffices');" style="width: 200px;" >
						</select>
					</td>
					<td align="right" style="width: 80px;">регион:&nbsp;</td>
					<td style="width: 220px;"> 
						<select name="nIDOffice" id="nIDOffice" style="width: 200px;" ></select>
					</td>
					<td style="width: 300px;">
						&nbsp;
					</td>
					<td style="width: 300px;">
					&nbsp;
				</td>
				</tr>

				<tr>
					<td align="right">тип обслужване:&nbsp;</td>
					<td>
						<select name="sTypeR" id="sTypeR" style="width: 200px;" >
							<option value="0">Всички</option>
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
					<td>
						&nbsp;
					</td>
				</tr>
				
				<tr>
					<td colspan="2" align="right">
						&nbsp;
					</td>
					<td colspan="2" align="right">
						<input type="checkbox" class="clear" checked="checked" id="nNoLimitCard" name="nNoLimitCard"/>
						Непривързани&nbsp;&nbsp;
						<input type="checkbox" class="clear" id="nActiveLC" name="nActiveLC"/>
						Неотработени Л.К.&nbsp;
					</td>
					<td align="left">
						<button type="button" name="Button" class="search" onClick="result();"><img src="images/confirm.gif">Търси</button>
					</td>
				</tr>		
					
			</table>
		</div>
	
		<div id="panel2">
			<table class="search">
				<tr>
					<td style="width: 300px;">
						&nbsp;
					</td>
					<td align="right" style="width: 110px">Статус:</td>
					<td>
						<select class="select100" name="sContractStatus" id="sContractStatus" />
							<option value="0">Всички</option>
							<option value="entered" selected="selected">Чакащи</option>
							<option value="validated">Валидирани</option>
							<option value="ignored">Отказани</option>
						</select>
					</td>
					<td align="right" style="width: 150px;">Населено място:</td>
					<td>
						<select class="select150" name="nIDCity" id="nIDCity" />
					</td>
				</tr>
				<tr>
					<td colspan="4">
						&nbsp;
					</td>
					<td align="right"><button name="Button" class="search" onclick="result();"><img src="images/confirm.gif">Търси</button></td>
				</tr>
		  	</table>
		</div>
<hr>

<div id="result" rpc_resize="yes" style="overflow: auto;"></div>

</form>
</div>

<script>
	onInit();
</script>
	