{literal}
<script>
	rpc_debug = true;

	function getResize() {
		var sheight = (document.body.clientHeight)-100;

		$('result').style.height = sheight;
	}
	function MoneyNomenclaturesDetailsView( id )
	{
			var dFrom		= $('sFromDate').value;
			var dTo			= $('sToDate').value;
			//var id			= $('hg').value;
			dialogMoneyNomenclaturesView(dFrom, dTo, id);
	}
</script>
{/literal}

<dlcalendar click_element_id="sFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="sToDate" 	input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form name="form1" id="form1" onsubmit="return false;" >
	<input type="hidden" id="hg" name="hg" value="0" />

	{include file='finance_operations_tabs.tpl'}

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">
			<div class="col-5 col-sm-5 col-lg-4">
				<div class="input-group input-group-sm" title="Период...">
					<div class="input-group-prepend">
						<i id="editFromDate" id="editToDate" class="fas fa-calendar-alt fa-fw" data-fa-transform="right-22 down-10" ></i>
					</div>
					<input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
					<div class="input-group-prepend">
						<i class="fas fa-arrows-h"></i>
					</div>
					<input type="text" name="sToDate" id="sToDate" class="form-control input-group-addon" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
					<div class="input-group-append">
						<i id="editToDate" class="fas fa-calendar-alt"></i>
					</div>
				</div>
			</div>
			<div class="col-5 col-sm-5 col-lg-2 pl-0">
				<div class="btn-group input-group-sm">
					<button class="btn btn-sm btn-primary" type="button" name="Button" onClick="loadXMLDoc2( 'result' );"><i class="fa fa-search fa-lg"></i> Търси &nbsp;</button>

				</div>
			</div>
			<div class="col-0 col-sm-0 col-lg-2">
				<div class="input-group input-group-sm">

				</div>
			</div>
			<div class="col-1 col-sm-1 col-lg-2">
				<div class="input-group input-group-sm">

				</div>
			</div>
			<div class="col-1 col-sm-1 col-lg-2 pl-0">
				<div class="btn-group input-group-sm">

				</div>
			</div>
			<div class="col-0 col-sm-0 col-lg-2 pl-3">
				<div class="input-group input-group-sm">

				</div>
			</div>
		</div>
	</div>

	<div id="result" rpc_paging="off" rpc_resize="off" style="overflow: auto;" ></div>
</form>

<script>
	getResize();
	
	loadXMLDoc2( 'result' );
</script>