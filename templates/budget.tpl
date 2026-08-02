{literal}
<script>
	rpc_debug = true;

	function budgetSearch()
	{
		loadXMLDoc2('search');
		return false;
	}

	function budgetLoadOffices()
	{
		loadXMLDoc2('load_offices');
	}

	function budgetExport()
	{
		dialogExcelDocBudget(
			$('id_firm').value,
			$('id_office').value,
			$('id_filter').value,
			$('month_from').value,
			$('month_to').value
		);
	}

	function budgetSave()
	{
		if (!confirm('Да бъде ли генериран бюджет за избрания начален месец?')) {
			return;
		}

		rpc_on_exit = function(errorCode)
		{
			rpc_on_exit = function() {};
			if (!errorCode) {
				budgetSearch();
			}
		};
		loadXMLDoc2('saveBudget');
	}

	function budgetInit()
	{
		rpc_on_exit = function(errorCode)
		{
			rpc_on_exit = function() {};
			if (!errorCode) {
				budgetSearch();
			}
		};
		loadXMLDoc2('init');
	}
</script>
{/literal}

<form name="form1" id="form1" class="ui-finance-tree-report ui-budget-report" onsubmit="return budgetSearch();">
	<ul class="nav nav-tabs nav-intelli ui-finance-tree-tabs">
		<li class="nav-item"><a class="nav-link active" href="#" aria-current="page">Бюджет</a></li>
		<li class="nav-item"><a class="nav-link" href="page.php?page=collections">Събираемост</a></li>
	</ul>

	<div class="ui-finance-tree-filters">
		<div class="ui-finance-tree-control ui-finance-tree-control-wide">
			<label for="id_firm">Фирма</label>
			<div class="input-group input-group-sm">
				<span class="input-group-prepend"><span class="input-group-text"><span class="ui-icon ui-icon-building" aria-hidden="true"></span></span></span>
				<select class="form-control" id="id_firm" name="id_firm" onchange="budgetLoadOffices();"></select>
			</div>
		</div>

		<div class="ui-finance-tree-control ui-finance-tree-control-wide">
			<label for="id_office">Регион</label>
			<div class="input-group input-group-sm">
				<span class="input-group-prepend"><span class="input-group-text"><span class="ui-icon ui-icon-location" aria-hidden="true"></span></span></span>
				<select class="form-control" id="id_office" name="id_office"><option value="0">-= Всички региони =-</option></select>
			</div>
		</div>

		<div class="ui-finance-tree-control ui-finance-tree-control-wide">
			<label for="id_filter">Филтър</label>
			<div class="input-group input-group-sm">
				<span class="input-group-prepend"><span class="input-group-text"><span class="ui-icon ui-icon-filter" aria-hidden="true"></span></span></span>
				<select class="form-control" id="id_filter" name="id_filter"><option value="0">.:: Изберете филтър ::.</option></select>
			</div>
		</div>

		<div class="ui-finance-tree-control">
			<label for="month_from">От месец</label>
			<select class="form-control form-control-sm" id="month_from" name="month_from"></select>
		</div>

		<div class="ui-finance-tree-control">
			<label for="month_to">До месец</label>
			<select class="form-control form-control-sm" id="month_to" name="month_to"></select>
		</div>

		<div class="ui-finance-tree-control">
			<label for="regions_view">Изглед</label>
			<select class="form-control form-control-sm" id="regions_view" name="regions_view">
				<option value="0">Обобщено</option>
				<option value="1">По региони</option>
			</select>
		</div>

		<div class="ui-finance-tree-actions">
			<button type="submit" class="btn btn-sm btn-primary"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
			<button type="button" class="btn btn-sm btn-success" onclick="budgetExport();"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Excel</button>
			<button type="button" class="btn btn-sm btn-info" onclick="dialogBudgetArchive();"><span class="ui-icon ui-icon-archive" aria-hidden="true"></span> Архив</button>
			<button type="button" class="btn btn-sm btn-warning" onclick="budgetSave();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Генерирай бюджет</button>
		</div>
	</div>

	<div id="result" class="ui-finance-tree-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>
</form>

<script>
	budgetInit();
</script>
