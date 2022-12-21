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
			var oldDate;

			if ( grd == 1 ) {
				oldDate = $('sSearchDate');
			} else {
				oldDate = $('sSearchDate');
			}

			var MM = oldDate.value.substr(0,2);
			var YY = oldDate.value.substr(3,4);

			if(YY == "0000") {
				var d = new Date();
				var m = d.getMonth()+1;
				var y = d.getFullYear();

				if(m <=9) {
					m = "0"+m;
				}

				oldDate.value = m + '.' + y;
				return
			}

			if ( act == 'next' ) {
				MM++;

				if ( MM == '13' ) {
					MM = '1';
					YY++;
				}
			} else {
				MM--;

				if ( MM == '0' ) {
					MM = '12';
					YY--;
				}
			}

			if ( MM < 10 ) {
				MM = "0" + MM;
			}

			oldDate.value = MM + '.' + YY;

			var year = $('year');
			var month = $('month');

			year.value = YY;
			month.value = MM;
		}
	</script>
	<style>
	        .container-fluid {
            height: 80% !important;
        }
    </style>
{/literal}

<form name="form1" id="form1" onsubmit="return false;" style="margin-bottom: 130px !important;">
<input type="hidden" id="id" name="id" value="{$id|default:0}" />
<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
<input type="hidden" id="idc" name="idc" value="0" />
<input type="hidden" id="sAct" name="sAct" value="1" />
<input type="hidden" id="sName" name="sName" value="{$person_name2}" />
<input type="hidden" id="sPdfName" name="sPdfName" value="" />

{include file='person_tabs.tpl'}

<div class="container-fluid" id="filter" style="overflow: auto;">
	<div class="row" id="filter_result">
		<div class="col-3 p-1 ml-2">
			<div class="input-group input-group-sm mb-1">
				<span class="input-group-addon " onclick="nextMonth(1, 'prev');" id="btnLeft">
							<i class="far fa-chevron-left"></i>
						</span>
				<div class="input-group-prepend">
					<span class="fa fa-calendar fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
				</div>
				<input class="form-control inp50" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;
				<input class="form-control inp75" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>
				<span class="input-group-append"  onclick="nextMonth(1, 'next');">
					<i class="far fa-chevron-right"></i>
				</span>
			</div>
		</div>
		<div class="col p-1">
			<div class="input-group input-group-sm mb-1">
				<button class="btn btn-sm btn-info ml-2" type="button" onClick="formSubmit(1); return false;" name="Button"><i class="far fa-list"></i> Подробна</button>
				<button class="btn btn-sm btn-info ml-1" type="button" onClick="formSubmit(2); return false;" name="Button"><i class="far fa-list-alt"></i> Обобщена</button>
				<button class="btn btn-sm btn-info ml-1" type="button" onClick="formSubmit(3); return false;" name="Button"><i class="far fa-home-alt"></i> Обекти</button>
			</div>
		</div>
		<div class="col-3 text-right p-1">
			{if $personnel_edit}
				<button class="btn btn-sm btn-success" id="b100" onClick="editSalary(0,1);"><i class="far fa-plus fa-lg"></i> Наработка</button>
				<button class="btn btn-sm btn-danger" id="b100" onClick="editSalary(0,0);"><i class="far fa-minus fa-lg"></i> Удръжка</button>
			{/if}
		</div>
	</div>

	<div class="row w-100 px-0" id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="overflow: auto;"></div>
 	<!-- край на работната част -->
</div>
<nav class="navbar fixed-bottom flex-row py-2 navbar-expand-lg p-2" id="search">
	<div class="col">
		{if $personnel_edit}
			<button class="btn btn-sm btn-danger mr-1"	type="button" onclick="openPDF();" > <i class="far fa-file-pdf" ></i> Пл. Фиш</button>
		{/if}
	</div>
	<div class="col">
		<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
			<label class="btn btn-sm p-2 btn-success" title="Наработки">
				<input type="checkbox" id="plus" name="plus" onclick="formSubmit(1);" checked /><i class="fas fa-plus-circle fa-lg"></i>
			</label>
			<input class="form-control inp75 mr-2" type="text" id="plus_price" name="plus_price" style="text-align: right;" readonly />
			<label class="btn btn-sm p-2 btn-danger" title="Удръжки">
				<input type="checkbox" id="minus" name="minus" class="clear" checked onclick="formSubmit(1); " /><i class="fas fa-minus-circle fa-lg"></i>
			</label>
			<input class="form-control inp75" type="text" id="minus_price" name="minus_price" style="text-align: right;" readonly />&nbsp;&nbsp;
		</div>
	</div>
	<div class="col text-right p-2">
		{if $personnel_edit}
			<button class="btn btn-sm btn-success mr-1"	onclick="onPrint('export_to_xls');" > <i class="far fa-file-excel" ></i> </button>
			<button class="btn btn-sm btn-danger mr-1"	 onclick="onPrint('export_to_pdf');" > <i class="far fa-file-pdf" ></i> </button>
		{/if}
		<button class="btn btn-sm btn-danger"	    onClick="window.close();"		><i class="far fa-window-close" ></i> Затвори </button>
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