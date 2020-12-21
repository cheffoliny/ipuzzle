{literal}
	<script>
	
		rpc_debug = true;
		
		function onInit()
		{
			rpc_on_exit = function()
			{
				var nTT = document.getElementById( "nTransfersTo" ).value;
				var nTF = document.getElementById( "nTransfersFrom" ).value;
				
				document.getElementById( "transferToMe" ).style.display = ( nTT == 0 ) ? "none" : "";
				document.getElementById( "transferFromMe" ).style.display = ( nTF == 0 ) ? "none" : "";
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( 'load' );
		}
		
		function getResult()
		{
			rpc_on_exit = function()
			{
				var nTT = document.getElementById( "nTransfersTo" ).value;
				var nTF = document.getElementById( "nTransfersFrom" ).value;
				
				document.getElementById( "transferToMe" ).style.display = ( nTT == 0 ) ? "none" : "";
				document.getElementById( "transferFromMe" ).style.display = ( nTF == 0 ) ? "none" : "";
				
				rpc_on_exit = function() {}
			}
			
			if( document.getElementById( "nRefreshTotals" ) )document.getElementById( "nRefreshTotals" ).value = "1";
			
			loadXMLDoc2( 'result' );
		}
		
		function openOrder( id )
		{
			if( id )
			{
				dialogOrder( 'id=' + id );
			}
		}
		
		function openBuy()
		{
			dialogBuy();
		}
		function openSale()
		{
			dialogSale();
		}
		function openSaleBook()
		{
			dialogSaleFromBook();
		}
		
		function openDoc( id, doc_type )
		{
			if( id )
			{
				switch( doc_type )
				{
					case 'buy':
						dialogBuy( 'id=' + id, 'buy_' + id );
						break;
					
					case 'sale':
						dialogSale2( id );
						break;
				}
			}
		}
		
		function setPayDeskReport()
		{
			dialogSetPayDeskReport();
		}
		
		function changeTimePeriod()
		{
			rpc_on_exit = function()
			{
				var nTT = document.getElementById( "nTransfersTo" 	).value;
				var nTF = document.getElementById( "nTransfersFrom" ).value;
				
				document.getElementById( "transferToMe" 	).style.display = ( nTT == 0 ) ? "none" : "";
				document.getElementById( "transferFromMe"	).style.display = ( nTF == 0 ) ? "none" : "";
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( 'loadTransfers' );
		}
		
		function enterConfirm()
		{
			if( event.keyCode == '13' )
			{
				searchDoc();
			}
		}
		
		function searchDoc()
		{
			rpc_on_exit = function()
			{
				if( $("nDocExists").value != "0" )
				{
					var nID = $("nDocExists").value;
					
					openDoc( nID, "sale" );
				}
				else
				{
					alert( "Не е намерен приходен документ с този номер!" )
				}
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( "checkDoc" );
		}
		
		function refreshForm( oCallerHandle )
		{
			rpc_on_exit = function()
			{
				if( oCallerHandle )oCallerHandle.focus();
				rpc_on_exit = function() {}
			}
			
			if( document.getElementById( "nRefreshTotals" ) )document.getElementById( "nRefreshTotals" ).value = "1";
			
			loadXMLDoc2( "result" );
		}
	</script>
	
	<style>
		table.total
		{
			font-size: 12px;
			margin: 0px;
			padding: 0px;
			border: 1px solid black;
			border-spacing: 0px;
			border-collapse: separate;
			background-color: D2DCF0;
			width: 330px;
		}
		input.total
		{
			border: 1px solid black;
			font-size: 12px;
			background-color: DCE6F0;
			width: 200px;
			height: 20px;
			text-align: right;
		}
	</style>
{/literal}

<dlcalendar click_element_id="sFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="sToDate" 	input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>


<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nTransfersTo" name="nTransfersTo" value="0" />
	<input type="hidden" id="nTransfersFrom" name="nTransfersFrom" value="0" />
	<input type="hidden" id="nDocExists" name="nDocExists" value="0" />
	<input type="hidden" id="nRefreshTotals" name="nRefreshTotals" value="0" />

	{include file="finance_operations_tabs.tpl"}

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fas fa-coins fa-fw" data-fa-transform="right-22 down-10" title="Изберете сметка..."></span>
					</div>
					<select class="form-control" name="nIDBankAccount" id="nIDBankAccount" title="Изберете сметка..."></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm" title="Период...">
					<div class="input-group-prepend">
						<i id="editFromDate" class="fas fa-calendar-alt fa-fw" data-fa-transform="right-22 down-10" ></i>
					</div>
					<input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
					<div class="input-group-prepend">
						<i class="fas fa-arrows-h"></i>
					</div>
					<input type="text" name="sToDate" id="sToDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
					<div class="input-group-append">
						<i id="editToDate" class="fas fa-calendar-alt"></i>
					</div>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="btn-group input-group-sm">
					<button class="btn btn-sm btn-primary mr-2" type="button" name="Button" onClick="getResult();"><i class="fa fa-search fa-lg"></i> Търси&nbsp; </button>

					<button id="btnGroupDrop1" type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						&nbsp; Приход / Разход
					</button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						<a class="dropdown-item dropdown-item-menu" title="Генериране на приходен документ от кочан..." onclick="openSaleBook();" 			>
							<i class="fa fa-plus"></i> &nbsp; От кочан </a>
						<a class="dropdown-item dropdown-item-menu" title="Генериране на приходен документ..." onclick="openSale();"			>
							<i class="fa fa-plus"></i> &nbsp; Приход </a>
						<a class="dropdown-item dropdown-item-menu" title="Генериране на разходен документ..."	onclick="openBuy();"	>
							<i class="fa fa-minus"></i> &nbsp; Разход </a>
					</div>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fas fa-coins fa-fw" data-fa-transform="right-22 down-10" title="Салдо по сметка..."></span>
					</div>
					<input class="form-control" type="text" id="nTotalStartBalance" name="nTotalStartBalance" placeholder="Начално..." disabled />
					<div class="input-group-append">
						<span data-fa-transform="right-22 down-10" title="Салдо по сметка...">лв.</span>
					</div>
					&nbsp;
					<div class="input-group-prepend">
						<span class="fas fa-coins fa-fw" data-fa-transform="right-22 down-10" title="Салдо по сметка..."></span>
					</div>
					<input type="text" class="form-control" id="nTotalEndBalance" name="nTotalEndBalance" placeholder="Крайно..." disabled />
					<div class="input-group-append">
						<span data-fa-transform="right-22 down-10" title="Салдо по сметка...">лв.</span>
					</div>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="btn-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-file-alt fa-fw"  data-fa-transform="right-22 down-10" title="№ на документ..."></span>
					</div>
					<input class="form-control" type="text" id="nDocNum" name="nDocNum" onkeypress="return formatDigits( event );" onkeyup="enterConfirm();" placeholder="№ на документ...." />
					<button class="btn btn-sm btn-primary" id="btnSearchDoc" onclick="searchDoc();"><i class="fa fa-search fa-lg"></i> Търси &nbsp;</button>

				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-3">
				<div class="input-group input-group-sm">
					{*<button type="button" id="hide"  onclick="hideDiv(0);" class="btn btn-sm btn-light mr-2"  style="display: none;"><i class="fa fa-compress fa-lg"></i></button>*}
					{*<button type="button" id="show"  onclick="fixFilter();" class="btn btn-sm btn-light mr-2"><i class="fa fa-expand fa-lg"></i></button>*}


				</div>
			</div>
		</div>

		{*<div id="filter" style="display: none;">*}
		{**}
		{*</div>*}
	</div>

	<div id="result" rpc_resize="on" style="overflow: auto;"></div>
</form>

<script>
	onInit();
</script>