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
		
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="id" name="id" value="{$id|default:0}" />
<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
<input type="hidden" id="idc" name="idc" value="0" />
<input type="hidden" id="sAct" name="sAct" value="1" />
<input type="hidden" id="sName" name="sName" value="{$person_name2}" />

<input type="hidden" id="sPdfName" name="sPdfName" value="" />

	{include file='person_tabs.tpl'}

	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
<tr>
	<td id="filter_result">
	<!-- начало на работната част -->
	<center>
		<table class="search">
			<tr>
				<td align="right" width="300px">
					Год
					<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
				<td align="center">
					<button type="button" onClick="formSubmit(1); return false;" name="Button"><img src="images/confirm.gif">Подробна</button>
				</td>
				<td align="center">
					<button type="button" onClick="formSubmit(2); return false;" name="Button"><img src="images/confirm.gif">Обобщена</button>
				</td>
				<td align="center">
					<button type="button" onClick="formSubmit(3); return false;" name="Button"><img src="images/confirm.gif">Обекти</button>
				</td>
				<td valign="top" align="right" width="400px">{if $personnel_edit}
					<button id="b100" onClick="editSalary(0,1);"><img src="images/plus.gif" />Наработка</button>
					<button id="b100" onClick="editSalary(0,0);"><img src="images/erase.gif" />Удръжка</button>
				{/if}</td>
			</tr>

	  </table>
	</center>

	<hr>
	
	<div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width: 1000px; height: 350px; overflow: auto;"></div>

 	<!-- край на работната част -->
	</td>
</tr>
</table>


<div id="search"  style="padding-top: 10px;width: 1000px;">
	<table width="100%" cellspacing="1px" class="search">
		<tr valign="top">
			<td>
			{if $personnel_edit}
				<button type="button" onclick="openPDF();" >Пл. Фиш</button>
			{/if}
			</td>
			<td width="700px" align="right">
				<input type="checkbox" id="plus" name="plus" class="clear" checked onclick="formSubmit(1);" /> наработки: &nbsp;
				<input type="text" id="plus_price" name="plus_price" style="width: 80px; text-align: right;" readonly />&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="checkbox" id="minus" name="minus" class="clear" checked onclick="formSubmit(1); " /> удръжки: &nbsp;
				<input type="text" id="minus_price" name="minus_price" style="width: 80px; text-align: right;" readonly />&nbsp;&nbsp;
			</td>
			<td valign="bottom" align="right" width="50px">
			{if $personnel_edit}
				<a href="#" onclick="onPrint('export_to_xls');"><img src="images/excel.gif" border="0" /></a>
			{/if}
			</td>
			<td valign="bottom" align="center" width="50px">
			{if $personnel_edit}
				<a href="#" onclick="onPrint('export_to_pdf');"><img src="images/pdf2.gif" border="0" /></a>
			{/if}
			</td>
			<td valign="top" align="right" width="100px">
				<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
			</td>
		</tr>
	</table>
</div>
<div id="NoDisplay" style="display:none"></div>
</form>

<script>loadXMLDoc('result');//loadMainData();</script>
	{if !$personnel_edit}
		
//		<script>
//		if( form=document.getElementById('form1') )  
//			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
//		</script>
	{/if}