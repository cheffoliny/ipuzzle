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
                        dialogBuy2(id);
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

{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form name="form1" id="form1" onsubmit="return false;" class="mb-5 ui-money-detail-report">
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
		<div class="row justify-content-start pl-3 pt-2 table-secondary ui-money-detail-toolbar ui-money-detail-toolbar-primary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-building" aria-hidden="true"></span></div>
					<select class="form-control select200" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'loadOffices' );" ></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-document" aria-hidden="true"></span></div>
					<select class="form-control select200" name="nIDOffice" id="nIDOffice" ></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm"><!-- Новото за Техн. обсл.  -->
					<div class="input-group-prepend"><span class="ui-icon ui-icon-arrow-right" aria-hidden="true"></span></div>
					<select class="form-control form-control-select200" name="nIDDirection" id="nIDDirection" ></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm ui-money-detail-object-filter">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-home" aria-hidden="true"></span></div>
					<input class="form-control inp50" type="text" id="nObjectNum" name="nObjectNum" suggest="suggest" queryType="objByNum" onkeypress="formatDigits( event );" onchange="onChangeObjectNum();" maxlength="12" placeholder=" №..." />
					<input class="form-control inp150" type="text" id="sObjectName" name="sObjectName" suggest="suggest" queryType="objByName" onchange="onChangeObject();" placeholder=" Име на обект..." />
				</div>
			</div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-list" aria-hidden="true"></span></div>
                    <select class="form-control select200" name="sOrderType" id="sOrderType" onchange="loadXMLDoc2( 'loadNomenclatures' );">
                        <option value="">-- Всички ордери --</option>
                        <option value="earning">Само Приходни</option>
                        <option value="expense">Само Разходни</option>
                    </select>
                </div>
            </div>
		</div>
		<div class="row justify-content-start pl-3 py-1 table-secondary ui-money-detail-toolbar ui-money-detail-toolbar-secondary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-card" aria-hidden="true"></span></div>
					<select class="form-control form-control-select200" name="nIDBankAccount" id="nIDBankAccount" ></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm"><!-- Новото за Техн. обсл.  -->
					<div class="input-group-prepend"><span class="ui-icon ui-icon-user" aria-hidden="true"></span></div>
					<input class="form-control form-control-inp200" type="text" id="sPersonName" name="sPersonName" suggest="suggest" queryType="personByName"  onchange="onChangePerson();" placeholder=" Касиер..." />
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm ui-money-detail-period">
					<div class="ui-money-detail-date-field">
						<button type="button" id="editFromDate" class="ui-money-detail-calendar" title="Изберете начална дата" aria-label="Изберете начална дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
						<input class="form-control inp50" type="text" name="sFromDate" id="sFromDate" onkeypress="return formatDate( event, '.' );" />
					</div>
					<span class="ui-money-detail-separator" aria-hidden="true"><span class="ui-icon ui-icon-exchange"></span></span>
					<div class="ui-money-detail-date-field">
						<button type="button" id="editToDate" class="ui-money-detail-calendar" title="Изберете крайна дата" aria-label="Изберете крайна дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
						<input class="form-control inp50" type="text" name="sToDate" id="sToDate" onkeypress="return formatDate( event, '.' );" />
					</div>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></div>
					<select class="form-control select200" name="sMonth" id="sMonth" ></select>
				</div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-list" aria-hidden="true"></span></div>
                    <select class="form-control select200" name="nIDNomenclature" id="nIDNomenclature" ></select>
                </div>
            </div>
		</div>
		<div class="row justify-content-start pl-3 pb-2 table-secondary ui-money-detail-toolbar ui-money-detail-toolbar-totals">
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-chart" aria-hidden="true"></span></div>
                    <input class="form-control inp50" type="text" id="nTotalExpense" name="nTotalExpense" disabled />
                    <span class="input-group-append text-danger"><span class="ui-icon ui-icon-minus" aria-hidden="true"></span></span>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend bg-success text-white"><span class="ui-icon ui-icon-chart" aria-hidden="true"></span></div>
                    <input class="form-control inp50" type="text" id="nTotalEarning" name="nTotalEarning" disabled />
                    <span class="input-group-append text-success"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span></span>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend bg-info text-white"><span class="ui-icon ui-icon-chart" aria-hidden="true"></span></div>
                    <input class="form-control inp50" type="text" id="nTotalChange" name="nTotalChange" disabled />
                    <span class="input-group-append"><span class="ui-icon ui-icon-equals" aria-hidden="true"></span></span>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">

            </div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-compact btn-light mr-2" title=" без ДДС">
                        <input type="checkbox" name="cDDS" id="cDDS" autocomplete="off" /><span class="ui-icon ui-icon-money" aria-hidden="true"></span>
                    </label>
                    <label class="btn btn-compact btn-light mr-2" title=" без ДДС">
                        <input type="checkbox" name="cTransfer" id="cTransfer" /><span class="ui-icon ui-icon-upload" aria-hidden="true"></span>
                    </label>

					{*{if $button}<button class="btn btn-sm btn-success" type="button" onClick="openIncomings();"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Постъпления </button>{/if}*}
					{if $button}<button class="btn btn-sm btn-primary" type="button" onClick="openOverview();"><span class="ui-icon ui-icon-calculator" aria-hidden="true"></span> Обобщена </button>{/if} &nbsp;
					<button class="btn btn-sm btn-info" type="button" name="Button" onClick="getResult();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси &nbsp; &nbsp; </button>

				</div>
			</div>
		</div>
	</div>
	{if $button}
		<div id="result" rpc_resize="on" class="pb-5 mb-5"></div>
	{else}
		<div id="result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="off" class="pb-5 mb-5"></div>

		<div id="search" class="w-100 fixed-bottom text-right p-2">
			<button class="btn btn-sm btn-danger" id="b100" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>

			<button type="button" class="btn btn-sm btn-danger" onclick="onPrint('export_to_pdf');"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> &nbsp;PDF </button>
			<button type="button" class="btn btn-sm btn-success" onclick="onPrint('export_to_xls');"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> EXCEL </button>
		</div>
	{/if}

</form>

<script>
    onInit();
</script>
