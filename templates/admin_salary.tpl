{literal}
	<script>
		rpc_debug = true;

		rpc_on_exit = function()
		{
			$('uplaoded_file_name').value="";
			$('uplaoded_file_type').value="";
		}
		
		InitSuggestForm = function  ()
		{			
			for(var i = 0; i < suggest_elements.length; i++) 
			{
//				if( suggest_elements[i]['id'] == 'firm' ) 
//				{
//					suggest_elements[i]['suggest'].setSelectionListener( onSuggestFirm );
//				}
//				if( suggest_elements[i]['id'] == 'region' ) 
//				{
//					suggest_elements[i]['suggest'].setSelectionListener( onSuggestRegion );
//				}
				if( suggest_elements[i]['id'] == 'region_object' )
				{
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
				}
			}
		}
		
//		function onSuggestFirm ( aParams ) 
//		{
//			$('id_firm').value = aParams.KEY;
//			$('region').value = '';
//			$('region_object').value = '';
//		}
//		
//		function onSuggestRegion ( aParams ) 
//		{
//			$('id_region').value = aParams.KEY;
//			$('region_object').value = '';
//		}
		
		function onSuggestObject ( aParams ) 
		{
			$('id_object').value = aParams.KEY;
		}
		
		function onFirmChange()
		{
			$('id_object').value = 0;
			$('region_object').value = '';
			loadXMLDoc( 'fillRegions' );
		}
		
		function onRegionChange()
		{
			$('id_object').value = 0;
			$('region_object').value = '';
		}
		
		function onRegionObjectChange()
		{
			$('id_object').value = 0;
		}
		
		function deleteSalary( id )
		{
			if( confirm('Наистина ли желаете да премахнете записа?') )
			{
				$('id').value = id;
				loadXMLDoc('delete_salary');
			}
		}
		
		function ImportSalary()
		{
			if( $("import_type").value == 'gsm')
				dialogImportSlaryGSM();
			else
				dialogImportSlary();
		}
		
		function personnel( id )
		{
			dialogPerson( id );
		}
		
		function openFixSalary() {
			dialogFixSalary();
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-salary-report ui-salary-admin-report" onSubmit="return loadXMLDoc('result')">
	<input type="hidden" id="id_object" name="id_object" value="0" />
	<input type="hidden" id="uplaoded_file_name" name="uplaoded_file_name" value="" />
	<input type="hidden" id="uplaoded_file_type" name="uplaoded_file_type" value="" />
	<input type="hidden" id="id" name="id" value="0">
	<input type="hidden" id="year_month" name="year_month" value="0">

	{include file='tabs_setup_personnel.tpl'}

	<div class="ui-salary-report-filters">
		<div class="row justify-content-start table-secondary ui-salary-report-toolbar">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-tag" title="Фирма на административно обслужване" aria-hidden="true"></span>
					</div>
					<select class="form-control" name="firm" id="firm" onchange="onFirmChange()" ></select>&nbsp;&nbsp;
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-tags" title="Регион" aria-hidden="true"></span>
					</div>
					<select class="form-control" name="region" id="region" onchange="onRegionChange()"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-home" title="Обект" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="region_object" id="region_object" type="text" suggest="suggest" queryType="region_object" queryParams="firm;region" onchange="onRegionObjectChange()" onpast="onRegionObjectChange()" placeholder="Обект..."/>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-4-5">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-users" title="Тип" aria-hidden="true"></span>
					</div>
					<select class="form-control" id="type" name="type">
						<option value="1">Служители от</option>
						<option value="2">За сметка на</option>
					</select>&nbsp;&nbsp;
				</div>
			</div>
			<div class="col-12 col-sm-8 col-lg-4">
				<div class="btn-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-calendar" title="Период" aria-hidden="true"></span>
					</div>
					<input class="form-control" size="2" onkeypress="return formatDigits(event);" name="month" id="month" type="number" min="1" max="12" step="1" value="{$month}"/>
					<input class="form-control mr-3" size="4" onkeypress="return formatDigits(event);" name="year" id="year" type="number" min="2016" max="2040" step="1" value="{$year}"/>
					<button class="btn btn-sm btn-primary" type="submit" name="Button"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
				</div>
			</div>
		</div>
		<div class="row justify-content-start pl-3 pt-1 pb-2 table-secondary">
			<select type="hidden" name="import_type" id="import_type" style="display: none;">
				<option value='salary'>Работни заплати</option>
			</select>

			<button style="display:none;" onclick="ImportSalary()">От Файл</button>
		</div>
	</div>

	<div id="result" class="ui-salary-report-result"></div>
	
</form>

<script>
	loadXMLDoc( 'fillFirms' );
</script>
