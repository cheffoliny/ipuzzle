{literal}
	<script>
		rpc_debug = true;
		
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
		
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="idc" name="idc" value="0" />
<input type="hidden" id="sAct" name="sAct" value="1" />
<input type="hidden" id="nIDLimitCard" name="nIDLimitCard" value="{$nIDLimitCard|default:0}" />

<div class="page_caption">Работна заплата на {$person_name}</div>

<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
<tr>
	<td>{include file=personal_card_tabs.tpl}</td>
</tr>
<tr>
	<td id="filter_result">
	<!-- начало на работната част -->
	<center>
		<table class="search" width="100%">
			<tr>
				<td align="center"></td>
			
				<td align="right" width="300px">
					Год
					<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
				<td align="center" style="width: 100px;">
					<button type="button" onClick="formSubmit(1); return false;" name="Button"><img src="images/confirm.gif">Подробна</button>
				</td>
				<td align="center" style="width: 100px;">
					<button type="button" onClick="formSubmit(2); return false;" name="Button"><img src="images/confirm.gif">Обобщена</button>
				</td>
				<td align="center" style="width: 100px;">
					<button type="button" onClick="formSubmit(3); return false;" name="Button"><img src="images/confirm.gif">Обекти</button>
				</td>
			</tr>

	  </table>
	</center>

	<hr>
	


 	<!-- край на работната част -->
	</td>
</tr>
</table>

	<div id="result"  rpc_excel_panel="on" rpc_paging="off" rpc_resize="on" style="overflow: auto;"></div>

<div id="NoDisplay" style="display:none"></div>
</form>

<script>
	loadXMLDoc('result');
</script>
