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

		function setReport(e) {
			//
			var jEl = jQuery(e);
			var sName = 'Фирми';
			jQuery(".dropdown-menu a").removeClass('active');

			jEl.parent().addClass('active');
			console.log(jEl.attr('id'));

			switch (jEl.attr('id')) {
				case 'firms':
					onClickRadioFirms();
					sName= 'Фирми';
					break;
				case 'regions':
					onClickRadioRegions();
					sName = 'Региони';
					break;
				case 'objects':
					onClickRadioObjects();
					sName = 'Обекти';
					break;
				default : alert("Проблем при подаване на тип справка!");
			}

			jQuery('#sNameTypeReport_name').html(sName);
		}


	</script>

{/literal}


<form name="form1" id="form1" onSubmit="getResult();return false;">
	<input type="hidden" name="show_filters" id="show_filters" value="show" />
	<input type="hidden" name="sIDOffices" id="sIDOffices" value="" />
	<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
	<input type="hidden" id="nRadio" name="nRadio" value="1" />
	<input type="hidden" id="sAct" name="sAct" value="1" />

	<input type="hidden" id="active" name="active" value="0" />

	{include file='tabs_setup_personnel.tpl'}

	<div class="table-secondary">
		<div class="row justify-content-start pl-3 pb-1 pt-2">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></i>
					</div>
					<select class="form-control" name="positions" id="positions"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-users fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></i>
					</div>
					<select class="form-control" id="type" name="type" onchange="onChangeType()">
						<option value="1"> Служители от... </option>
						<option value="2"> За сметка на... </option>
					</select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="btn-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-filter fa-fw" data-fa-transform="right-22 down-10" title="Филтър"></i>
					</div>
					<select class="form-control" name="schemes" id="schemes" ></select>
					<button id="btnGroupDrop1" type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

					</button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						<a class="dropdown-item dropdown-item-menu" name="Button5"	id="b25" title="Нов филтър" 			onClick="openFilter( 1 );" 			>
							<i class="fas fa-plus"></i> &nbsp; Добави </a>
						<a class="dropdown-item dropdown-item-menu" name="Button4"	id="b25" title="Редактиране на филтър" 	onClick="openFilter( 2 );"			>
							<i class="far fa-edit"></i> &nbsp; Редактирай </a>
						<a class="dropdown-item dropdown-item-menu" name="Button3"	id="b25" title="Премахване на филтър"	onClick="deleteFilter( schemes );"	>
							<i class="far fa-trash-alt"></i> &nbsp; Изтрий </a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="btn-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-calendar fa-fw" data-fa-transform="right-22 down-10" title="Филтър"></i>
					</div>
					<input class="form-control" size="2" onkeypress="return formatDigits(event);" name="month" id="month" type="number" min="1" max="12" step="1" value="{$month}"/>
					<input class="form-control" size="4" onkeypress="return formatDigits(event);" name="year" id="year" type="number" min="2016" max="2040" step="1" value="{$year}"/>
				</div>
				<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
					<label class="btn btn-compact btn-light mr-1" title="Фирми" onClick="onClickRadioFirms(); showFilters();">
						<input type="radio" id="type" name="types" value="rFirms" checked="checked" />
						<i class="fas fa-tag fa-lg text-primary pt-1 pr-1"></i>
					</label>
					<label class="btn btn-compact btn-light mr-1" title="Региони" onClick="onClickRadioRegions(); showFilters();" >
						<input type="radio" id="type" name="types" value="rRegions" />
						<i class="fas fa-tags fa-lg text-primary pt-1 pr-1"></i>
					</label>
					<label class="btn btn-compact btn-light mr-2" title="Обекти" onClick="onClickRadioObjects(); showFilters();">
						<input type="radio" id="type" name="types" value="rObjects" />
						<i class="fas fa-home fa-lg text-primary pt-1 pr-1"></i>
					</label>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">

					<button type="button" class="btn btn-sm btn-primary mr-1" onClick="getResult(2);" name="Button2" id="button2"><i class="far fa-tags"></i> По региони</button>
					<button type="button" class="btn btn-sm btn-primary mr-1" onClick="getResult(1);" name="Button1" id="button1"><i class="far fa-users"></i> По служители</button>
					<button type="button"  class="btn btn-sm btn-light mr-2" onClick="showFilters();"><i class="fa fa-compress fa-lg"></i></button>
				</div>
			</div>
		</div>
		<div id="filters" class="table-responsive pt-2 pb-2 mb-2">

		<div id="filters_center" class="col-4">
			<div id="pFirms">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></i>
					</div>
					<select class="form-control" name="all_firms" id="all_firms" style="height: 87px !important;" ondblclick="move_option_to( 'all_firms', 'account_firms', 'right');" multiple></select>
					<button class="btn btn-sm btn-success py-5" name="button" title="Добави фирма" onClick="move_option_to( 'all_firms', 'account_firms', 'right'); return false;">
						<i class="far fa-angle-right"></i>
					</button>
					<button class="btn btn-sm btn-danger py-5" name="button" title="Премахни фирма" onClick="move_option_to( 'all_firms', 'account_firms', 'left'); return false;">
						<i class="far fa-angle-left"></i>
					</button>
					<select class="form-control" name="account_firms[]" id="account_firms" style="height: 87px !important;" ondblclick="move_option_to( 'all_firms', 'account_firms', 'left');" multiple></select>
				</div>
			</div>

			<div id="pRegions">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></i>
					</div>
					<select class="form-control" name="all_regions" id="all_regions" style="height: 87px !important;"  ondblclick="move_option_to( 'all_regions', 'account_regions', 'right');" multiple></select>
					<button class="btn btn-sm btn-success py-5" name="button" title="Добави регион" onClick="move_option_to( 'all_regions', 'account_regions', 'right'); return false;">
						<i class="far fa-angle-right"></i>
					</button>
					<button class="btn btn-sm btn-danger py-5" name="button" title="Премахни регион" onClick="move_option_to( 'all_regions', 'account_regions', 'left'); return false;">
						<i class="far fa-angle-left"></i>
					</button>
					<select class="form-control" name="account_regions[]" id="account_regions" style="height: 87px !important;" ondblclick="move_option_to( 'all_regions', 'account_regions', 'left');" multiple></select>
				</div>
			</div>

			<div id="pObjects">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></i>
					</div>
					<input class="form-control suggest inp50" type="number" size="6" min="0" step="1" name="sNum" id="sNum" suggest="suggest" queryParams="sIDOffices" queryType="objByNum" onkeypress="return formatDigits(event);" placeholder="N: обект"/>

					<input class="form-control suggest inp75" type="text" name="sName" id="sName"  suggest="suggest" queryParams="sIDOffices" queryType="objByName" placeholder="Име на обект" />
					<button class="btn btn-sm btn-success py-5" name="button" title="Добави обект" onClick="addObject()" >
						<i class="far fa-angle-right"></i>
					</button>
					<button class="btn btn-sm btn-danger py-5" name="button" title="Премахни обект" onClick="removeObject()">
						<i class="far fa-angle-left"></i>
					</button>

					<select class="form-control" name="account_objects[]" id="account_objects" style="height: 87px !important;" ondblclick="removeObject()" multiple></select>
				</div>
			</div>


			{*</center>*}
		</div>

	</div>
		</div>
	</div>

	<div id="result"></div>

</form>

<script>
	onInit();
</script>
