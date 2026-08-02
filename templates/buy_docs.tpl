{literal}
	<script>
		rpc_debug = true;

		// Show Hide Extended Search options
        var filterVisible = false;

        function hideDiv( load ) {
            var e = document.getElementById("filter");
            var h = document.getElementById("hide");
            var s = document.getElementById("show");

            e.style.display	= "none";
            h.style.display = "none";
            s.style.display = "block";
            filterVisible	= false;

            if ( load == 1 ) {
                loadXMLDoc('result');
            }
        }

        function fixFilter() {
            var e = document.getElementById("filter");
            var h = document.getElementById("hide");
            var s = document.getElementById("show");

            switch( filterVisible ) {
                case true:
                    hideDiv(1);
                    break;

                case false:
                    e.style.display	= "block";
                    h.style.display = "block";
                    s.style.display = "none";
                    filterVisible	= true;
                    break;
            }
        }
        // End Show Hide Extended Search

		function onInit()
		{
			rpc_on_exit = function()
			{
				loadXMLDoc2( "result" );
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( "load" );
		}
		
		function submitForm() {
			$('subm').value = 'yes';
			
			loadXMLDoc2('result');
			
			rpc_on_exit = function() {
				$('subm').value = 'no';
			}			
		}

        function openFilter(type) {
			var id;
			if(type == 1) {
				dialogBuyDocsFilter(0);
			} else {
				id = $('schemes').value;
				if(id != 0) {
					dialogBuyDocsFilter(id);
				}
			}
		}
		
		function just_do_it() {
			
			switch($('sel').value) {
				case 'mark_all':
					if ( confirm('Наистина ли желаете да маркирате всички записи?') ) {
						checkAll( true );
					}
				break;
				case 'unmark_all':
					if ( confirm('Наистина ли желаете да отмаркирате всички записи?') ) {
						checkAll( false );
					}
				break;	
				case '':
				break;
				default:
					group_sale($('sel').value);  	//loadXMLDoc2('group_buy',1);
			}
		}
		
		function group_sale(id) {
			var e 	= document.getElementsByTagName("input");
			var str = '';
			
			for ( var i = 0; i < e.length; i++ ) {
				if ( e[i].type == 'checkbox' ) {
					if ( e[i].checked == true ) {
						
						if ( str.length > 0 ) {
							str += ';;' + e[i].name;
						} else {
							str = e[i].name;
						}
					}
				}
			}

			dialogGroupBuyesPayOrders(str, id);	
		}		
		
		function deleteFilter(schemes)
		{
			if(schemes.value > 0)
				if( confirm('Наистина ли желаете да премахнeте филтърът?') )
				{
					loadXMLDoc('deleteFilter',6);
				}
		}
		
		function openBuy() {
			dialogBuy();
		}

		function openBuyDoc(id) {
			//dialogBuy('id='+id, 'buy_'+id);
			dialogBuy2(id);
		}
		
		function enterConfirm() {
			if ( event.keyCode == '13' ) {
				submitForm();
			}
		}
		
		InitSuggestForm = function() {
			for ( var i = 0; i < suggest_elements.length; i++ ) {
				switch ( suggest_elements[i]['id'] ) {
					case 'sClientName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestClient );
					break;
				}
			}
		}
			
		function onSuggestClient( aParams ) {
			var aParts = aParams.KEY.split(';');
			
			$('nIDClient').value = aParts[0];
			$('sClientName').value = aParts[2];
		}	
		
		function resetClient() {
			$('nIDClient').value = 0;
		}			
	</script>

{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form id="form1" action="" class="ui-nomenclature-list ui-finance-docs-list ui-buy-docs-list" onsubmit="return false;">
	<input type="hidden" id="subm" name="subm" value="no" />
	<input type="hidden" id="nIDClient" name="nIDClient" value="0" />
	
	{include file="finance_operations_tabs.tpl"}

	<div class="ui-finance-docs-filters">
		<div class="row justify-content-start table-secondary ui-finance-docs-toolbar ui-buy-docs-toolbar">
			<div class="col-6 col-sm-4 col-lg-2 ui-buy-docs-firm">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-tag" title="Фирма администратор" aria-hidden="true"></span>
					</div>
					<select class="form-control" name="nIDFirm" id="nIDFirm" title="Фирма администратор"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0 ui-buy-docs-client">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-user" title="Клиент..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" id="sClientName" name="sClientName" suggest="suggest" queryType="ClientName" onchange="resetClient()" onpast="resetClient()" placeholder="Клиент..." title="Търсене по клиент - част от име или директно избран" />
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0 ui-buy-docs-period">
				<div class="input-group input-group-sm ui-finance-docs-period" title="Период...">
					<button type="button" id="editFromDate" class="ui-finance-date-trigger" title="Начална дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
					<input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
					<span class="ui-finance-date-separator"><span class="ui-icon ui-icon-exchange" aria-hidden="true"></span></span>
					<input type="text" name="sToDate" id="sToDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
					<button type="button" id="editToDate" class="ui-finance-date-trigger" title="Крайна дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 ui-buy-docs-number">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-document" title="№ на документ..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" id="nNum" name="nNum" onkeypress="return formatNumber(event);" onkeyup="enterConfirm();" placeholder="№ на документ..." title="Търсене по част от № на документ..." />
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0 ui-buy-docs-filter">
				<div class="btn-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-filter" title="Филтър" aria-hidden="true"></span>
					</div>
					<select class="form-control" name="schemes" id="schemes" ></select>
					<button id="btnGroupDrop1" type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						<a href="#" class="dropdown-item dropdown-item-menu" name="Button5" id="buyFilterAdd" title="Нов филтър" onClick="openFilter( 1 ); return false;">
							<span class="ui-icon ui-icon-plus" aria-hidden="true"></span> &nbsp; Добави </a>
						<a href="#" class="dropdown-item dropdown-item-menu" name="Button4" id="buyFilterEdit" title="Редактиране на филтър" onClick="openFilter( 2 ); return false;">
							<span class="ui-icon ui-icon-edit" aria-hidden="true"></span> &nbsp; Редактирай </a>
						<a href="#" class="dropdown-item dropdown-item-menu" name="Button3" id="buyFilterDelete" title="Премахване на филтър" onClick="deleteFilter( schemes ); return false;">
							<span class="ui-icon ui-icon-delete" aria-hidden="true"></span> &nbsp; Изтрий </a>
					</div>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-3 ui-buy-docs-actions">
				<div class="input-group input-group-sm">
					<button type="button" name="Button" class="btn btn-sm btn-danger" onclick="openBuy();"><span class="ui-icon ui-icon-minus" aria-hidden="true"></span> Разход </button>
					<button type="button" name="Button" class="btn btn-sm btn-primary" onclick="submitForm(); hideDiv(1);"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси &nbsp;</button>
				</div>
			</div>
		</div>

		{*<div id="filter" style="display: none;">*}
			{**}
		{*</div>*}
	</div>

	<div id="result" class="ui-finance-docs-result"></div>

</form>

<script>
	onInit();
</script>
