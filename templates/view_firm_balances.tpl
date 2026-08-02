{literal}
<script>
	rpc_debug = true;

</script>
{/literal}

<dlcalendar click_element_id="editFromDate" input_element_id="sFromDate" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" input_element_id="sToDate" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" class="ui-finance-summary-report ui-firm-balances-report" onSubmit="return false;">

	{include file='finance_operations_tabs.tpl'}

	<div class="ui-finance-summary-filters">
		<div class="row justify-content-start table-secondary ui-finance-summary-toolbar">
			<div class="col-12 col-md-8 col-lg-5">
				<div class="input-group input-group-sm ui-finance-summary-period" title="Период...">
					<button type="button" id="editFromDate" class="ui-finance-date-trigger" title="Начална дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
					<input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
					<span class="ui-finance-date-separator"><span class="ui-icon ui-icon-exchange" aria-hidden="true"></span></span>
					<input type="text" name="sToDate" id="sToDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
					<button type="button" id="editToDate" class="ui-finance-date-trigger" title="Крайна дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</div>
			</div>
			<div class="col-12 col-md-4 col-lg-2">
				<div class="btn-group input-group-sm">
					<button class="btn btn-sm btn-primary" type="button" name="Button" onClick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
				</div>
			</div>
		</div>
	</div>

	<div id="result" class="ui-finance-summary-result" rpc_paging="off" rpc_resize="off"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>
