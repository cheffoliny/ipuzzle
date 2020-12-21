{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		function getResult() {
			loadXMLDoc2('result',1);
		}
		function openPerson( id ) {
			var spl = id.split(',');
			var nIDPerson = spl[1];
			dialogPerson( nIDPerson );
		}
		function openContractPDF( id ) {
			var spl = id.split(',');
			var contract_id = spl[0];
			$('id_contract').value = contract_id;
			loadDirect('export_to_pdf')
		}
		
		function ignoreContract( id ) {
			if ( confirm('Наистина ли желаете да откажете този договор?') ) {
				var spl = id.split(',');
				var contract_id = spl[0];
				$('id_contract').value = contract_id;
				loadXMLDoc('ignoreContract',1);
			}	
		}
		
		function openRequest( id ) {
			var spl = id.split(',');
			var contract_id = spl[0];
			dialogObjectToContract(contract_id);
		}
		
	</script>
{/literal}

<dlcalendar click_element_id="img_date_from" input_element_id="date_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_to" input_element_id="date_to" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="id_contract" name="id_contract" value = "0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Задачи - ЕЛЕКТРОННИ ДОГОВОРИ</td>
			<td class="buttons">
			<!--	<button onclick="editAuto( 0 );"><img src="images/plus.gif"> Добави </button> -->
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Статус:</td>
				<td>
					<select class="select100" name="sContractStatus" id="sContractStatus" />
				</td>
				<td align="right">Населено място:</td>
				<td>
					<select class="select150" name="nIDCity" id="nIDCity" />
				</td>
				
				<td align="right">от дата:</td>
				<td>
					<input type="text" id="date_from" name="date_from" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_from" />
				</td>
				<td align="right">до дата:</td>
				<td>
					<input type="text" id="date_to" name="date_to" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_to" />
				</td>
				
				
				<td align="right"><button name="Button" onclick="getResult();"><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}