{literal}
	<script>
        rpc_debug = true;
        rpc_method = 'POST';

        InitSuggestForm = function() {
            for ( var i = 0; i < suggest_elements.length; i++ ) {
                switch( suggest_elements[i]['id'] ) {
                    case 'nObjectNum':
                    case 'sObjectName':
                        suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
                        break;

                    case 'sPersonName':
                        suggest_elements[i]['suggest'].setSelectionListener( onSuggestPersonByName );
                        break;
                }
            }
        };

        function onSuggestObject( aParams ) {
            var aStuff = aParams.KEY.split(';');

            $('nIDObject').value 	= aStuff[0];
            $('nObjectNum').value 	= aStuff[1];
            $('sObjectName').value 	= aStuff[2];
        }

        function onSuggestPersonByName( aParams ) {
            var aStuff = aParams.KEY.split(';');

            $('nIDPerson').value 	= aStuff[0];
            $('sPersonName').value 	= aStuff[2];
        }

        function onChangeObjectNum() {
            $('nIDObject').value 	= 0;
            $('sObjectName').value 	= "";
        }

        function onChangeObject() {
            $('nIDObject').value 	= 0;
            $('nObjectNum').value 	= "";
        }

        function onChangePerson() {
            $('nIDPerson').value 	= 0;
        }

        function onInit() {
            $('start').value = 1;

            loadXMLDoc2('load');

            rpc_on_exit = function() {
                $('start').value 			= 0;
                $('nRefreshTotals').value 	= 1;

                loadXMLDoc2('result');

                rpc_on_exit = function() {};
            }
        }

        function openOrder(id) {
            if ( id ) {
                dialogOrder( 'id=' + id );
            }
        }

        function getResult() {
            $('nRefreshTotals').value = 1;

            loadXMLDoc2('result');

            return true;
        }

        function openOverview() {
            var nIDFirm 	= $('nIDFirm').value;
            var nIDOffice 	= $('nIDOffice').value;
            var dFrom		= $('sFromDate').value;
            var dTo			= $('sToDate').value;

            dialogMoneyNomenclaturesOverview(nIDFirm, nIDOffice, dFrom, dTo);
            return true;
        }

        function openIncomings() {
            var nIDFirm	 	= $('nIDFirm').value;
            var nIDOffice 	= $('nIDOffice').value;
            var dFrom		= $('sFromDate').value;
            var dTo			= $('sToDate').value;
            var	aFrom		= [];
            var	aTo			= [];

            aFrom			= dFrom.split('.');
            aTo				= dTo.split('.');

            var from 		= aFrom[2] + '-' + aFrom[1];
            var to 			= aTo[2] + '-' + aTo[1];

            dialogIncomings(nIDFirm, nIDOffice, from, to);
            return true;
        }

        function processSaldo() {
            var nIDFirm 	= $('nIDFirm');
            var nIDOffice	= $('nIDOffice');
            var cTransfer	= $('cTransfer');
            var nIDSaldo	= $('nIDSaldo').value;

            if ( parseInt(nIDSaldo) > 0 ) {
                nIDFirm.value = 0;
                nIDOffice.value = 0;
                cTransfer.checked = true;
                cTransfer.disabled = true;
                nIDFirm.disabled = true;
                nIDOffice.disabled = true;
            } else {
                cTransfer.checked = false;
                cTransfer.disabled = false;
                nIDFirm.disabled = false;
                nIDOffice.disabled = false;
            }
        }

        function openDoc(id, doc_type) {

            if (id) {
                switch(doc_type) {
                    case "buy":
                        dialogBuy('id='+id, 'buy_'+id);
                        break;

                    case "sale":
                        dialogSale2(id);
                        break;

                    default:
                        break;
                }
            }
        }

        function onPrint(type) {
            loadDirect(type);
        }

        function openFilter( type )
        {
            var id;
            if( type == 1 ) { dialogViewMoneyNomenclaturesDetailFilter( 0 ); }
            else
            {
                id = $('schemes').value;
                if( id != 0 ) { dialogViewMoneyNomenclaturesDetailFilter( id ); }
            }
        }

        function deleteFilter( schemes )
        {
            if( schemes.value > 0 )
                if( confirm( 'Наистина ли желаете да премахнeте филтърът?' ) )
                {
                    rpc_on_exit = function()
                    {
                        rpc_on_exit = function()
                        {
                            rpc_on_exit = function() {};

                            loadXMLDoc2( 'result' );
                        };

                        loadXMLDoc2( 'load' );
                    };

                    loadXMLDoc2( 'deleteFilter' );
                }
        }

	</script>

	<style>
		table.total
		{
			font-size: 12px;
			margin: 0;
			padding: 0;
			border: 1px solid black;
			border-spacing: 0;
			border-collapse: separate;
			background-color: #D2DCF0;
			width: 300px;
		}
		input.total
		{
			border: 1px solid black;
			font-size: 12px;
			background-color: #DCE6F0;
			width: 200px;
			height: 20px;
			text-align: right;
		}
	</style>
{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
	<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />
	<input type="hidden" id="nRefreshTotals" name="nRefreshTotals" value="0" />
	<input type="hidden" id="firm" name="firm" value="{$nIDFirm}" />
	<input type="hidden" id="office" name="office" value="{$nIDOffice}" />
	<input type="hidden" id="object" name="object" value="{$nIDObject}" />
	<input type="hidden" id="dtype" name="dtype" value="{$nIDType}" />
	<input type="hidden" id="date_from" name="date_from" value="{$sFromDate}" />
	<input type="hidden" id="date_to" name="date_to" value="{$sToDate}" />
	<input type="hidden" id="month" name="month" value="{$sMonth}" />
	<input type="hidden" id="nomenclature" name="nomenclature" value="{$nIDNomenclature}" />
	<input type="hidden" id="start" name="start" value="0" />
	<input type="hidden" id="nBankAccount" name="nBankAccount" value="{$nIDBankAccount}" />

	{include file="finance_operations_tabs.tpl"}

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">

			<table class="table-sm table-borderless ml-3">
				<tr>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="far fa-building"></i></div>
							<select class="form-control select200" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'loadOffices' );" ></select>
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="far fa-file-alt"></i></div>
							<select class="form-control select200" name="nIDOffice" id="nIDOffice" ></select>
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm"><!-- Новото за Техн. обсл.  -->
							<div class="input-group-prepend"><i class="far fa-arrow-alt-from-left "></i></div>
							<select class="form-control form-control-select200" name="nIDDirection" id="nIDDirection" ></select>
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="far fa-home"></i></div>
							<input class="form-control inp50" type="text" id="nObjectNum" name="nObjectNum" suggest="suggest" queryType="objByNum" onkeypress="formatDigits( event );" onchange="onChangeObjectNum();" maxlength="12" placeholder=" №..." />
							<input class="form-control inp150" type="text" id="sObjectName" name="sObjectName" suggest="suggest" queryType="objByName" onchange="onChangeObject();" placeholder=" Име на обект..." />
						</div>
					</td>
				</tr>

				<tr>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="fas fa-credit-card"></i></div>
							<select class="form-control form-control-select200" name="nIDBankAccount" id="nIDBankAccount" ></select>
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm"><!-- Новото за Техн. обсл.  -->
							<div class="input-group-prepend"><i class="far fa-user-tag"></i></div>
							<input class="form-control form-control-inp200" type="text" id="sPersonName" name="sPersonName" suggest="suggest" queryType="personByName"  onchange="onChangePerson();" placeholder=" Касиер..." />
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend" id="editFromDate" title="Изберете дата" style="cursor: pointer;"><i class="far fa-calendar-alt"></i></div>
							<input class="form-control inp50" type="text" name="sFromDate" id="sFromDate" onkeypress="return formatDate( event, '.' );" />
							<span class="input-group-append"><i class="far fa-arrows-h"></i></span>
							<input class="form-control inp50" type="text" name="sToDate" id="sToDate" onkeypress="return formatDate( event, '.' );" />
							<span class="input-group-append mr-2" id="editToDate" title="Изберете дата" style="cursor: pointer;"><i class="far fa-calendar-alt"></i></span>
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend" id="editFromDate" title="Изберете дата" style="cursor: pointer;"><i class="far fa-calendar-alt"></i></div>
							<select class="form-control select200" name="sMonth" id="sMonth" ></select>
						</div>
					</td>
				</tr>

				<tr>
					<td class="pb-2">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="far fa-list"></i></div>
							<select class="form-control select200" name="sOrderType" id="sOrderType" onchange="loadXMLDoc2( 'loadNomenclatures' );">
								<option value="">-- Всички ордери --</option>
								<option value="earning">Само Приходни</option>
								<option value="expense">Само Разходни</option>
							</select>
						</div>
					</td>
					<td class="pb-2">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="far fa-list-alt"></i></div>
							<select class="form-control select200" name="nIDNomenclature" id="nIDNomenclature" ></select>
						</div>
					</td>
					<td class="pb-2">
						<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-compact btn-light mr-2" title=" без ДДС">
								<input type="checkbox" name="cDDS" id="cDDS" autocomplete="off" /><i class="fas fa-euro-sign fa-lg"></i>
							</label>
							<label class="btn btn-compact btn-light mr-2" title=" без ДДС">
								<input type="checkbox" name="cTransfer" id="cTransfer" /><i class="fas fa-upload fa-lg"></i>
							</label>
						</div>
					</td>
					<td class="pb-2">
						<div class="input-group input-group-sm justify-content-end">
							{*{if $button}<button class="btn btn-sm btn-success" type="button" onClick="openIncomings();"><i class="far fa-plus" ></i> Постъпления </button>{/if}*}
							{if $button}<button class="btn btn-sm btn-primary" type="button" onClick="openOverview();"><i class="far fa-abacus" ></i> Обобщена </button>{/if} &nbsp;
							<button class="btn btn-sm btn-info" type="button" name="Button" onClick="getResult();"><i class="far fa-search" ></i> Търси &nbsp; &nbsp; &nbsp; </button>

						</div>

					</td>
				</tr>

				<tr>
					<td class="pt-2 border-top border-secondary">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><i class="far fa-chart-line-down"></i></div>
							<input class="form-control inp50" type="text" id="nTotalExpense" name="nTotalExpense" disabled />
							<span class="input-group-append"><i class="far fa-minus-circle text-danger"></i></span>
						</div>
					</td>
					<td class="pt-2 border-top border-secondary">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend bg-success text-white"><i class="far fa-chart-line"></i></div>
							<input class="form-control inp50" type="text" id="nTotalEarning" name="nTotalEarning" disabled />
							<span class="input-group-append"><i class="far fa-plus-circle text-success"></i></span>
						</div>
					</td>
					<td class="pt-2 border-top border-secondary">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend bg-info text-white"><i class="far fa-chart-area fa-fw" data-fa-transform="right-22 down-10"></i></div>
							<input class="form-control inp50" type="text" id="nTotalChange" name="nTotalChange" disabled />
							<span class="input-group-append"><i class="far fa-equals"></i></span>
						</div>
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</div>
	</div>
	{if $button}
		<div id="result" rpc_resize="on" style="overflow: auto;"></div>
	{else}
		<div id="result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="off" style="overflow: auto; height: 360px;"></div>

		<div id="search" class="w-100 fixed-bottom text-right p-2">
			<button class="btn btn-sm btn-danger" id="b100" onClick="window.close();"><i class="far fa-times"></i> Затвори </button>

			<button type="button" class="btn btn-sm btn-danger" onclick="onPrint('export_to_pdf');"><i class="far fa-file-pdf-o"></i> &nbsp;PDF </button>
			<button type="button" class="btn btn-sm btn-success" onclick="onPrint('export_to_xls');"><i class="far fa-file-excel-o"></i> EXCEL </button>
		</div>
	{/if}

</form>

<script>
    onInit();
</script>