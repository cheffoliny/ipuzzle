{literal}

	<script>
		
		rpc_debug = true;
	
		InitSuggestForm = function() {
			for(var i=0; i<suggest_elements.length; i++) {
				switch( suggest_elements[i]['id'] ) {
					case 'sNum':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
					case 'sName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
				}
			}
		}
		
		function onSuggestObject( aParams ) {
			var aParts = aParams.KEY.split(';');
			
			$('nIDObject').value = 	aParts[0];
			$('sNum').value = 		aParts[1];
			$('sName').value =		aParts[2];
		}
		
		function onInit() {
			Filters	= document.getElementById('filters')
			filtersCenter 	= document.getElementById('filters_center')
			
			attachEventListener( $('sNum'),  "keypress", onKeyPressObjectNum);
			attachEventListener( $('sName'), "keypress", onKeyPressObjectName);
			
			Filters.removeChild(filtersCenter)
			Filters.appendChild(filtersCenter)
			
			$('pRegions').style.display = "none";
			$('pObjects').style.display = "none";
			
			
			loadXMLDoc2('load');
		}
		
		function onKeyPressObjectNum() {
			var regions;
			regions = $('account_regions[]');
			$('sIDOffices').value = 0;
			if( regions.length != 0 ) {
				$('sIDOffices').value = regions[0].value;
			}
			for (var i=1; i<regions.length;i++) {
				$('sIDOffices').value = $('sIDOffices').value+","+regions[i].value;
			}
			$('nIDObject').value = "";
			$('sName').value = "";
		}
		
		function onKeyPressObjectName() {
			var regions;
			regions = $('account_regions[]');
			$('sIDOffices').value = 0;
			if( regions.length != 0 ) {
				$('sIDOffices').value = regions[0].value;
			}
			for (var i=1; i<regions.length;i++) {
				$('sIDOffices').value = $('sIDOffices').value+","+regions[i].value;
			}
			
			$('nIDObject').value = "";
			$('sNum').value = "";
		}
		
		function showFilters() {
			if(	$('show_filters').value == "show") {
				$('filters').style.display = "none";
				$('show_filters').value = "hide";
				
			} else {
				$('filters').style.display = "block";
				$('show_filters').value = "show";
			}
			
			if(typeof(xslResizer) == 'function') {
				xslResizer();
			}
		}
		
		function onClickRadioFirms() {
			$('nRadio').value = '1';
			
			$('pFirms').style.display = "block";
			$('pRegions').style.display = "none";
			$('pObjects').style.display = "none";
		}
		
		function onClickRadioRegions() {
			$('nRadio').value = '2';
			
			$('pFirms').style.display = "none";
			$('pRegions').style.display = "block";
			$('pObjects').style.display = "none";
		}
			
		function onClickRadioObjects() {
			$('nRadio').value = '3';
			
			$('pFirms').style.display = "none";
			$('pRegions').style.display = "none";
			$('pObjects').style.display = "block";
		}
		
		function addObject() {
			var id_obj;
			id_obj = $('nIDObject').value;
			
			if( id_obj == "0") {
				alert('Изберете обект');
			} else {
				var obj = document.createElement('OPTION');
				obj.value = id_obj;
				obj.text = '['+$('sNum').value+']'+$('sName').value;
				$('account_objects').add(obj);
				$('nIDObject').value = "0";
				$('sNum').value = "";
				$('sName').value = "";
			}
		}
		
		function removeObject() {
			var obj = $('account_objects');
			while(obj.selectedIndex != -1) {
				obj.remove(obj.selectedIndex);
			}
		}
		
		function getResult(type) {
			if($('sfield'))	$('sfield').value = "";
			$('sAct').value = type;
			
			switch($('nRadio').value) {
				case '1': select_all_options('account_firms');break;
				case '2': select_all_options('account_regions');break;
				case '3': select_all_options('account_objects');break;
			}
			
			loadXMLDoc2('result',1);
			$('filters').style.display = "none";
			$('show_filters').value = "hide";
		}
		
		function onChangeType() {
			
			if($('sfield'))	$('sfield').value = "";
			
			if($('type').value == 1) {
				$('button2').disabled = false;	
			} else {
				$('button2').disabled = true;
			}
		}
		
		function personnel( id ) {
			dialogPerson( id );
		}
		
		function openSalary(id) {
			var sMonth,sYear;
			
			sMonth = $('month').value;
			sYear  = $('year').value;
			
			dialogPersonSalary(id,sMonth,sYear);
		}
		
		function openFilter(type) {
			var id;
			if(type == 1) {
				dialogAdminSalaryTotalFilter(0);
			} else {
				id = $('schemes').value;
				if(id != 0) {
					dialogAdminSalaryTotalFilter(id);
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

		
	</script>
	
	<style>
		table.result td, 
		table.result th 
		{
			padding: 1px 10px 1px 20px;
			border-spacing: 0px !important;
			border: 0px !important;
			margin: 0px !important;
		}
		
	</style>
{/literal}


<form name="form1" id="form1" onSubmit="getResult();return false;">
	<input type="hidden" name="show_filters" id="show_filters" value="show">
	<input type="hidden" name="sIDOffices" id="sIDOffices" value="">	
	<input type="hidden" id="nIDObject" name="nIDObject" value="0">
	<input type="hidden" id="nRadio" name="nRadio" value="1">
	<input type="hidden" id="sAct" name="sAct" value="1">

	<table class="page_data">
		<tr>
			<td class="page_name">Администрация - РАБOТНИ ЗАПЛАТИ ( Обобщена )</td>
			
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
				
			<td style="width:100px;">
				&nbsp;
			</td>
			
			<td style="width: 120px;">
				<button type="button" onClick="getResult(2);" name="Button2" id="button2"><img src="images/confirm.gif">По региони</button>
			</td>	
			<td style="width: 120px;">
				<button type="button" onClick="getResult(1);" name="Button1" id="button1"><img src="images/confirm.gif">По служители</button>
			</td>	
			<td align="right">
				<button type="button" style="width: 30px;" onClick="showFilters();"><img src="images/search2.gif"></button>
			</td>
		</tr>
	</table>
	
	<div id="filters">
	
	<center id="filters_center">
		<table class="search">
			<tr>
				<td>Тип</td>
				<td align="left">
					<select id="type" name="type" class="select110" onchange="onChangeType()"> 
						<option value="1">Служители от
						<option value="2">За сметка на
					</select>&nbsp;&nbsp;
				</td>
				
				<td align="right" style="width: 60px;">
					фирми
				</td>
				<td style="width: 20px;">
					<input type="radio" class="clear" id="type" name="types" value="rFirms" checked="checked" onClick = "onClickRadioFirms();" />
				</td>
				
				<td align="right" style="width: 60px;">
					региони
				</td>
				<td style="width: 20px;">
					<input type="radio" class="clear" id="type" name="types" value="rRegions" onClick = "onClickRadioRegions();" />
				</td>
				<td align="right" style="width: 60px;">
					обекти
				</td>
				<td align="left" style="width: 80px;">
					<input type="radio" class="clear" id="type" name="types" value="rObjects" onClick = "onClickRadioObjects();"/>
				</td>
				
				<td>Длъжност</td>
				<td>
					<select style="width:250px;" name="positions" id="positions"/>
				</td>
				
				<td style="width: 40px;">
					&nbsp;
				</td>
				
				<td align="center">
					Год
					<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
			</tr>
		</table>

		
		<div id="pFirms">
		<table class="search">
			<tr>	
				<td style="width:900px;" colspan="5" valign="top" align="center">
					<fieldset style="width: 850px;">
					<legend>Избор на фирми</legend>
						<table>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
							<tr class="even">
								<td>
									<select name="all_firms" id="all_firms" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_firms', 'account_firms', 'right');" multiple>
									</select>
								</td>
								<td>
									<button class="search" style="width: 50px;" name="button" title="Добави фирма" onClick="move_option_to( 'all_firms', 'account_firms', 'right'); return false;"><img src="images/mright.gif" /></button></br>
									<button name="button" style="width: 50px;" title="Премахни фирма" onClick="move_option_to( 'all_firms', 'account_firms', 'left'); return false;"><img src="images/mleft.gif" /></button>
								</td>
								<td>
									<select name="account_firms[]" id="account_firms" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_firms', 'account_firms', 'left');" multiple>
									</select>
								</td>
							</tr>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
		</div>
		
		
		<div id="pRegions">
		<table class="search">
			<tr>	
				<td style="width:900px;" colspan="5" valign="top" align="center">
					<fieldset style="width: 850px;">
					<legend>Избор на региони</legend>
						<table>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
							<tr class="even">
								<td>
									<select name="all_regions" id="all_regions" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_regions', 'account_regions', 'right');" multiple>
									</select>
								</td>
								<td>
									<button class="search" style="width: 50px;" name="button" title="Добави регион" onClick="move_option_to( 'all_regions', 'account_regions', 'right'); return false;"><img src="images/mright.gif" /></button></br>
									<button name="button" style="width: 50px;" title="Премахни регион" onClick="move_option_to( 'all_regions', 'account_regions', 'left'); return false;"><img src="images/mleft.gif" /></button>
								</td>
								<td>
									<select name="account_regions[]" id="account_regions" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_regions', 'account_regions', 'left');" multiple>
									</select>
								</td>
							</tr>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
		</div>
		
		<div id="pObjects">
		<table class="search">
			<tr>
				<td style="width:500px;">
					<fieldset style="width: 450px;">
					<legend>Избор на обекти</legend>
					<table class="input">
						<tr class="odd">
							<td align="right">Номер:</td>
							<td>
								<input type="text" name="sNum" id="sNum" class="inp50" suggest="suggest" queryParams="sIDOffices" queryType="objByNum" onkeypress="return formatDigits(event);"/>
							</td>
							<td align="right">Име:</td>
							<td colspan="3">
								<input type="text" name="sName" id="sName" style="width:270px;"  suggest="suggest" queryParams="sIDOffices" queryType="objByName"/>
							</td>
							
							
							<td>
								<button class="search" style="width: 50px;" name="button" title="Добави обект" onClick="addObject()"><img src="images/mright.gif" /></button></br>
								<button name="button" style="width: 50px;" title="Премахни обект" onClick="removeObject()"><img src="images/mleft.gif" /></button>
							</td>
							<td>
								<select name="account_objects[]" id="account_objects" size="10" style="width: 350px;" ondblclick="removeObject()" multiple>
								</select>
							</td>
							
							
						</tr>
					</table>
					</fieldset>
				</td>			
			
			</tr>
			
		</table>
		</div>
		
		
	</center>
	
	</div>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	onInit();
</script>
