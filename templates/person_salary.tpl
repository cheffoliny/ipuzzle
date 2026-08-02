{literal}
	<script>
		rpc_debug = true;
		rpc_method = 'POST';
		
		function submit_form() {
			loadXMLDoc( 'save', 0 );
		}
		
		function update_image() {
			var id = document.getElementById('id').value;
			if ( id == 0 ) {
				alert('Служитела все още не е създаден!');
			} else {
				dialogUpload( id );
			}
		}

		function delSalary(id) {
			document.getElementById('idc').value = id;
			if ( confirm('Наистина ли желаете да премахнете начислението?') ){
				loadXMLDoc('delete', 1);
			}
			document.getElementById('idc').value = 0;
		}
		
		function editSalary(id, type) {
			var id_person = document.getElementById('id').value;
			var year = document.getElementById('year').value;
			var month = document.getElementById('month').value;
			dialogNewSalary( id, id_person, month, year, type );
		}
		
		function formSubmit(type) {
			document.getElementById('sAct').value = type;
			loadXMLDoc('result');
		}
		
		function onPrint(type) {
			loadDirect(type);
		}
		
		function checkAll( bChecked ) {
		var aCheckboxes = document.getElementsByTagName('input');
		
		for( var i=0; i<aCheckboxes.length; i++ ) {
			if( aCheckboxes[i].type.toLowerCase() == 'checkbox' ) {
				aCheckboxes[i].checked = bChecked;
			}
		}
	}
	
	function just_do_it() {
		switch (getById('sel').value) {
			case '1':
				checkAll( true );
				break;
			case '2':
				checkAll( false );
				break;
			case '3':
				if ( confirm('Наистина ли желаете да премахнете начислението?') ) {
					loadXMLDoc('delete', 1);
				}
				break;
		}
	}
		
	function openPDF() {
		loadDirect('openTicket', 'L');
	}

		function nextMonth(grd, act) {
			var year = $('year');
			var month = $('month');
			var MM = parseInt(month.value, 10);
			var YY = parseInt(year.value, 10);

			if (!MM || !YY) {
				var currentDate = new Date();
				MM = currentDate.getMonth() + 1;
				YY = currentDate.getFullYear();
			}

			MM += act == 'next' ? 1 : -1;
			if (MM > 12) { MM = 1; YY++; }
			if (MM < 1) { MM = 12; YY--; }

			year.value = YY;
			month.value = MM < 10 ? '0' + MM : MM;
		}
	</script>
{/literal}

<form name="form1" id="form1" class="ui-salary-report ui-person-salary-report" onsubmit="return false;">
<input type="hidden" id="id" name="id" value="{$id|default:0}" />
<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
<input type="hidden" id="idc" name="idc" value="0" />
<input type="hidden" id="sAct" name="sAct" value="1" />
<input type="hidden" id="sName" name="sName" value="{$person_name2}" />
<input type="hidden" id="sPdfName" name="sPdfName" value="" />

{include file='person_tabs.tpl'}

<div class="container-fluid ui-person-salary-content" id="filter">
	<div class="row ui-salary-report-toolbar" id="filter_result">
		<div class="col-3 p-1 ml-2">
			<div class="input-group input-group-sm mb-1">
				<button type="button" class="ui-salary-month-button" onclick="nextMonth(1, 'prev');" id="btnLeft" title="Предходен месец"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
				<div class="input-group-prepend">
					<span class="ui-icon ui-icon-calendar" title="Период" aria-hidden="true"></span>
				</div>
				<input class="form-control inp50" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;
				<input class="form-control inp75" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>
				<button type="button" class="ui-salary-month-button" onclick="nextMonth(1, 'next');" title="Следващ месец"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button>
			</div>
		</div>
		<div class="col p-1">
			<div class="input-group input-group-sm mb-1">
				<button class="btn btn-sm btn-info ml-2" type="button" onClick="formSubmit(1); return false;" name="Button"><span class="ui-icon ui-icon-list" aria-hidden="true"></span> Подробна</button>
				<button class="btn btn-sm btn-info ml-1" type="button" onClick="formSubmit(2); return false;" name="Button"><span class="ui-icon ui-icon-layout" aria-hidden="true"></span> Обобщена</button>
				<button class="btn btn-sm btn-info ml-1" type="button" onClick="formSubmit(3); return false;" name="Button"><span class="ui-icon ui-icon-home" aria-hidden="true"></span> Обекти</button>
			</div>
		</div>
		<div class="col-3 text-right p-1">
			{if $personnel_edit}
				<button type="button" class="btn btn-sm btn-success" id="addSalaryEarning" onClick="editSalary(0,1);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Наработка</button>
				<button type="button" class="btn btn-sm btn-danger" id="addSalaryExpense" onClick="editSalary(0,0);"><span class="ui-icon ui-icon-minus" aria-hidden="true"></span> Удръжка</button>
			{/if}
		</div>
	</div>

	<div class="row w-100 px-0 ui-salary-report-result" id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>
 	<!-- край на работната част -->
</div>
<nav class="navbar fixed-bottom flex-row navbar-expand-lg ui-salary-action-bar" id="search">
	<div class="col">
		{if $personnel_edit}
			<button class="btn btn-sm btn-danger mr-1" type="button" onclick="openPDF();"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> Пл. Фиш</button>
		{/if}
	</div>
	<div class="col">
		<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
			<label class="btn btn-sm p-2 btn-success" title="Наработки">
				<input type="checkbox" id="plus" name="plus" onclick="formSubmit(1);" checked /><span class="ui-icon ui-icon-plus" aria-hidden="true"></span>
			</label>
			<input class="form-control inp75 mr-2" type="text" id="plus_price" name="plus_price" style="text-align: right;" readonly />
			<label class="btn btn-sm p-2 btn-danger" title="Удръжки">
				<input type="checkbox" id="minus" name="minus" class="clear" checked onclick="formSubmit(1);"><span class="ui-icon ui-icon-minus" aria-hidden="true"></span>
			</label>
			<input class="form-control inp75" type="text" id="minus_price" name="minus_price" style="text-align: right;" readonly />&nbsp;&nbsp;
		</div>
	</div>
	<div class="col text-right p-2">
		{if $personnel_edit}
			<button type="button" class="btn btn-sm btn-success mr-1" onclick="onPrint('export_to_xls');" title="Експорт в Excel"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span></button>
			<button type="button" class="btn btn-sm btn-danger mr-1" onclick="onPrint('export_to_pdf');" title="Експорт в PDF"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span></button>
		{/if}
		<button type="button" class="btn btn-sm btn-danger" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
	</div>
</nav>

<div id="NoDisplay" style="display:none"></div>
</form>
<br/>
<br/><br/><br/>
<script>loadXMLDoc('result');//loadMainData();</script>
	{if !$personnel_edit}
		
//		<script>
//		if( form=document.getElementById('form1') )  
//			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
//		</script>
	{/if}
