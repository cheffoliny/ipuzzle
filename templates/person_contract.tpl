{literal}
	<script>
		rpc_debug = true;

		function submit_form() {
			loadXMLDoc('save', 1);

		}

		function tsalary(id) {
			var check 	= id.checked;
			var name 	= id.name;

			if ( name == 'fix_salary' ) {
				var obj = $('fix_cost');

				if ( check == false ) {
					obj.value 		= '0.00';
					obj.disabled 	= true;
				} else {
					obj.disabled 	= false;
				}
			}

			if ( name == 'min_salary' ) {
				var obj = $('min_cost');

				if ( check == false ) {
					obj.value 		= '0.00';
					obj.disabled 	= true;
				} else {
					obj.disabled 	= false;
				}
			}

			document.getElementById('type_salary').value = name;
		}

	</script>
{/literal}


<dlcalendar click_element_id="img_trial_from" input_element_id="trial_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_trial_to" input_element_id="trial_to" tool_tip="Изберете дата"></dlcalendar>


<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="id" name="id" value="{$id|default:0}" />
	<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
	<input type="hidden" id="type_salary" name="type_salary" value="" />

	{include file='person_tabs.tpl'}

	<div class="row clearfix mt-2 px-1">
		<div class="col-4">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Заплата </div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Други фактори</div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> ... </div>
		</div>
	</div>
	<div class="row"id="filter">
		<div class="col-4 pl-3">
			<div class="input-group input-group-sm mb-1">
				<input class="form-control px-0 mx-0" type="checkbox" id="fix_salary" name="fix_salary" onclick="tsalary(this);" checked />
				<div class="input-group-prepend" id="sFixSalary">
					<span class="far fa-euro-sign" data-fa-transform="right-22 down-10" title="Фиксирана заплата"></span>
				</div>
				<input class="form-control w-75 text-right pr-4" type="text" name="fix_cost" id="fix_cost" onKeyPress="return formatMoney(event);" title="Фиксирана заплата" />
				<div class="input-group-append">
					лв.
				</div>
			</div>

			<div class="input-group input-group-sm mb-1">
				<input class="form-control px-0 mx-0" type="checkbox" id="min_salary" name="min_salary" onclick="tsalary(this);" /> &nbsp;
				<div class="input-group-prepend" id="sMinSalary" />
					<span class="fa fa-euro" data-fa-transform="right-22 down-10" title="Основна по ТД"></span>
				</div>
				<input class="form-control w-75 text-right pr-4" name="min_cost" type="text" id="min_cost" onKeyPress="return formatMoney(event);" title="Основна по ТД" placeholder="Основна по ТД..." />
				<div class="input-group-append">
					лв.
				</div>
			</div>

			<div class="input-group input-group-sm mb-1 pl-5">
				<div class="input-group-prepend" id="sInsurance">
					<span class="far fa-minus-square" data-fa-transform="right-22 down-10" title="Мин. осигурителен праг"></span>
				</div>
				<input class="form-control w-75 text-right pr-4" type="text" name="insurance" id="insurance" onKeyPress="return formatMoney(event);" title="Мин. осигурителен праг" placeholder="Мин. осигурителен праг..." />
				<div class="input-group-append">
					лв.
				</div>
			</div>
		</div>

		<div class="col-4 pl-3">
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="sFactor" >
					<span class="fa fa-calendar-check" data-fa-transform="right-22 down-10" title="Фактор за техници..."></span>
				</div>
				<input class="form-control text-right" type="text" name="factor" id="factor" onKeyPress="return formatMoney(event);" title="Фактор за техници..." placeholder="Фактор за техници" />
			</div>
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="sShiftsFactor">
					<span class="fa fa-calendar-check" data-fa-transform="right-22 down-10" title="Фактор за смени..."></span>
				</div>
				<input class="form-control text-right" type="text" name="shifts_factor" id="shifts_factor" style="width: 55px; text-align: right;" onKeyPress="return formatMoney(event);" title="Фактор за смени..."  placeholder="Фактор за смени" />
			</div>

			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend">
					<span class="fa fa-calendar" data-fa-transform="right-22 down-10" title="Прослужено време..."></span>
				</div>
				<input class="form-control text-right" type="text" name="serve" id="serve" title="Прослужено време..."  placeholder="Прослужено време" />
			</div>
		</div>
	</div>

	<!-- начало на работната част -->

	<div class="row clearfix mt-2 px-1">
		<div class="col-8">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Пробен период </div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Процент базово възнаграждение</div>
		</div>
	</div>
	<div class="row px-2">
		<div class="col-4 pl-3">
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="img_trial_from">
					<span class="fa fa-calendar-check" data-fa-transform="right-22 down-10" title="Oт дата..."></span>
				</div>
				<input class="form-control" name="trial_from" id="trial_from" type="text" onKeyPress="return formatDate(event, '.');" maxlength="10" title="От дата..." />
			</div>
		</div>
		<div class="col-4 pl-3">
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="img_trial_to">
					<span class="fa fa-calendar-times" data-fa-transform="right-22 down-10" title="До дата..."></span>
				</div>
				<input class="form-control" name="trial_to" id="trial_to" type="text" onKeyPress="return formatDate(event, '.');" maxlength="10" title="До дата..." />
			</div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="img_trial_to">
					<span class="fa fa-percentage" data-fa-transform="right-22 down-10" title="Процент базово възнаграждение..."></span>
				</div>
				<input class="form-control" name="rate_reward" id="rate_reward" type="text" onKeyPress="return formatDigits(event);" maxlength="3" />
			</div>
		</div>
	</div>

	<div class="row clearfix mt-2 px-1">
		<div class="col-4">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Образование </div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Трудов стаж </div>
		</div>
		<div class="col">
			<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Клас </div>
		</div>
	</div>
	<div class="row clearfix px-2">
		<div class="col-4">
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="img_trial_to">
					<span class="fas fa-book" data-fa-transform="right-22 down-10" title="Образование..."></span>
				</div>
				<input class="form-control" type="text" name="sEducation" id="sEducation" title="Образование" placeholder="Образование..." />
			</div>
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="img_trial_to">
					<span class="far fa-book" data-fa-transform="right-22 down-10" title="Специалност..."></span>
				</div>
				<input class="form-control" type="text" name="sSpeciality" id="sSpeciality"  title="Специалност.." placeholder="Специалност.." />
			</div>
			<div class="input-group input-group-sm mb-1">
				<div class="input-group-prepend" id="img_trial_to">
					<span class="far fa-book" data-fa-transform="right-22 down-10" title="Друга специалност..."></span>
				</div>
				<input class="form-control" type="text" name="sSpecialityOther" id="sSpecialityOther" title="Друга специалност.." placeholder="Друга специалност.." />
			</div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm">
				<input class="form-control inp50" type="text" name="nLOSDays" id="nLOSDays" onKeyPress="return formatDigits( event );" />
				<span class="input-group-append mr-2">д.</span>
				<input class="form-control inp50" type="text" name="nLOSMonths" id="nLOSMonths" onKeyPress="return formatDigits( event );" />
				<span class="input-group-append mr-2">м.</span>
				<input class="form-control inp100" type="text" name="nLOSYears" id="nLOSYears" onKeyPress="return formatDigits( event );" />
				<span class="input-group-append">г.</span>
			</div>
		</div>
		<div class="col-4">
			<div class="input-group input-group-sm">
				<div class="input-group-prepend" id="img_trial_to">
					<span class="fa fa-percent" data-fa-transform="right-22 down-10" title="Клас..."></span>
				</div>
				<input class="form-control" type="text" name="nClass" id="nClass" size="2" onKeyPress="return formatMoney( event );" />
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



<script>
	loadXMLDoc('result');//loadMainData();
</script>

{if !$personnel_edit}

	<script>
		if( form=document.getElementById('form1') )
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
	</script>
{/if}