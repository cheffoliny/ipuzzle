{literal}
<script>
	rpc_debug = true;

	function collectionsSearch()
	{
		loadXMLDoc2('search');
		return false;
	}

	function collectionsLoadOffices()
	{
		loadXMLDoc2('load_offices');
	}

	function collectionsExport()
	{
		dialogExcelDocCollections(
			$('id_firm').value,
			$('id_office').value,
			$('id_filter').value,
			$('month_from').value,
			$('month_to').value
		);
	}

	function collectionsInit()
	{
		rpc_on_exit = function(errorCode)
		{
			rpc_on_exit = function() {};
			if (!errorCode) {
				collectionsSearch();
			}
		};
		loadXMLDoc2('init');
	}
</script>
{/literal}

<form name="form1" id="form1" class="ui-finance-tree-report ui-collections-report" onsubmit="return collectionsSearch();">
	<ul class="nav nav-tabs nav-intelli ui-finance-tree-tabs">
		<li class="nav-item"><a class="nav-link" href="page.php?page=budget">Бюджет</a></li>
		<li class="nav-item"><a class="nav-link active" href="#" aria-current="page">Събираемост</a></li>
	</ul>

	<div class="ui-finance-tree-filters">
		<div class="ui-finance-tree-control ui-finance-tree-control-wide">
			<label for="id_firm">Фирма</label>
			<div class="input-group input-group-sm">
				<span class="input-group-prepend"><span class="input-group-text"><span class="ui-icon ui-icon-building" aria-hidden="true"></span></span></span>
				<select class="form-control" id="id_firm" name="id_firm" onchange="collectionsLoadOffices();"></select>
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
			<button type="button" class="btn btn-sm btn-success" onclick="collectionsExport();"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Excel</button>
		</div>
	</div>

	<div class="ui-finance-tree-summary" aria-live="polite">
		<span>Общо:</span>
		<output id="total_earning">0 €</output>
	</div>

	<div id="result" class="ui-finance-tree-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>
</form>

<script>
	collectionsInit();
</script>
