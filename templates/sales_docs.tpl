{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			//attachEventListener( $('sClientName'), "keypress", onKeyPressClient);
			
			rpc_on_exit = function() {
				loadXMLDoc2("result");
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( "load" );
		}
		
		function formSubmit() {
			$('subm').value = 'yes';
			
			loadXMLDoc2('result');
			
			rpc_on_exit = function() {
				$('subm').value = 'no';
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
				case 'gen_pdfs':
					loadXMLDoc2('gen_pdfs');
				break;
				case '':
				break;
				default:
					group_sale($('sel').value); //loadXMLDoc2('group_sale',1);
			}
		}
		
		function group_sale(id) {
			var e 	= document.getElementsByTagName("input");
			var str = '';
			
			for ( var i = 0; i < e.length; i++ ) {
				if ( e[i].type == 'checkbox' ) {
					if ( e[i].checked == true ) {
						//e[i].disabled = true;
						
						if ( str.length > 0 ) {
							str += ';;' + e[i].name;
						} else {
							str = e[i].name;
						}
					}
				}
			}

			//if ( str.length > 0 ) {
				dialogGroupSalesPayOrders(str, id);
			//} else {
			//	alert('Няма избрани задължения!');
			//}			
		}
		
		function openFilter(type) {
			var id;
			if(type == 1) {
				dialogSalesDocsFilter(0);
			} else {
				id = $('schemes').value;
				if(id != 0) {
					dialogSalesDocsFilter(id);
				}
			}
		}
	
		function deleteFilter(schemes) {
			if(schemes.value > 0)
				if( confirm('Наистина ли желаете да премахнeте филтърът?') ) {
					loadXMLDoc('deleteFilter',6);
				}
		}
		
		function openSaleDoc(id) {
			dialogSale2(id);
		}
		
		function openSales() {
			dialogSale();
		}
		
		function enterConfirm() {
			if( event.keyCode == '13' )
			{
				formSubmit();
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

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="subm" name="subm" value="no" />
	<input type="hidden" id="nIDClient" name="nIDClient" value="0" />

	{include file='finance_operations_tabs.tpl'}

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						{*Администрация:&nbsp;*}
						<span class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></span>
					</div>
					<select class="form-control" name="nIDFirm" id="nIDFirm" title="Фирма на административно обслужване"></select>
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
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw"  data-fa-transform="right-22 down-10" title="Търсене по клиент - част от име или директно избран"></span>
					</div>
					<input class="form-control suggest" type="text" id="sClientName" name="sClientName" suggest="suggest" queryType="ClientName" onchange="resetClient()" onpast="resetClient()" placeholder="Клиент..." title="Търсене по клиент - част от име или директно избран" />
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fa fa-barcode fa-fw"  data-fa-transform="right-22 down-10" title="Търсене по част от номер"></i>
					</div>
					<input class="form-control" type="text" id="nNum" name="nNum" onkeypress="return formatNumber(event);" onkeyup="enterConfirm();" placeholder="№ на документ..." title="Търсене по част от номер" />
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="btn-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fas fa-filter fa-fw" data-fa-transform="right-22 down-10" title="Филтър"></span>
					</div>
					<select class="form-control" name="schemes" id="schemes" ></select>
					<button id="btnGroupDrop1" type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						<a class="dropdown-item dropdown-item-menu" name="Button5"	id="b25" title="Нов филтър" 			onClick="openFilter( 1 );" 			>
							<i class="fas fa-plus"></i> &nbsp; Добави </a>
						<a class="dropdown-item dropdown-item-menu" name="Button4"	id="b25" title="Редактиране на филтър" 	onClick="openFilter( 2 );"			>
							<i class="far fa-edit"></i> &nbsp; Редактирай </a>
						<a class="dropdown-item dropdown-item-menu" name="Button3"	id="b25" title="Премахване на филтър"	onClick="deleteFilter( schemes );"	>
							<i class="far fa-trash-alt"></i> &nbsp; Изтрий </a>
					</div>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-3">
				<div class="input-group input-group-sm">
					{*<button type="button" id="hide"  onclick="hideDiv(0);" class="btn btn-sm btn-light mr-2"  style="display: none;"><i class="fa fa-compress fa-lg"></i></button>*}
					{*<button type="button" id="show"  onclick="fixFilter();" class="btn btn-sm btn-light mr-2"><i class="fa fa-expand fa-lg"></i></button>*}
					<button class="btn btn-sm btn-success ml-2 mr-2" onclick="openSales();"><i class="fa fa-plus fa-lg"></i> Добави </button>
					<button class="btn btn-sm btn-primary" onclick="formSubmit();"><i class="fa fa-search fa-lg"></i> Търси</button>
				</div>
			</div>
		</div>
	</div>
	<div id="result"></div>

</form>

<script>
	onInit();
</script>