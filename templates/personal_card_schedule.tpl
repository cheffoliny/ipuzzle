{literal}
	<script>
		rpc_debug = true;
		
		function getResult() {
			$('id_person').value = parent.$('nID').value;
			loadXMLDoc2('result');
		}
		
		function nextDate(act) {
			var oldDate = $('date').value;
			var oldDD = oldDate.substr(0,2);
			var oldMM = oldDate.substr(3,2);
			var oldYY = oldDate.substr(6,4);
			
			var newDate = new Date();
			newDate.setFullYear(oldYY,oldMM-1,oldDD);
			
			if(act == 'next')
				newDate.setDate(newDate.getDate() + 1);
			else
				newDate.setDate(newDate.getDate() - 1);
			
			var newDD = newDate.getDate();
			var newMM = newDate.getMonth()+1;
			var newYY = newDate.getYear();
			
			if( newDD < 10 ) newDD = "0" + newDD;
			if( newMM < 10 ) newMM = "0" + newMM;
			
			var sDate = newDD+'.'+newMM+'.'+newYY;		
			$('date').value = sDate;
			getResult();
		
		}
		
		function refreshIFrames(id) {
			parent.document.getElementById('nIDLimitCard').value = id;
			parent.document.getElementById('personal_card_limit_card').src = 'page.php?page=personal_card_limit_card&id_limit_card='+id;
			parent.document.getElementById('personal_card_operations').src = 'page.php?page=personal_card_operations&id_limit_card='+id;
		}
		
	</script>

{/literal}
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id_person" id="id_person" value="0">

	<table class="page_data" width="100%">
		<tr style="height:35px;">
			<td valign="middle" >
				<button onclick="nextDate('prev');"style="width:10px"><img src="images/mleft.gif" /></button>
			</td>
			<td valign="middle" align="center">
				<input style="width:100px; font-size:15px;font-weight: bold; " id="date" name="date" type="text" class="clear" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" value="{$sDate}" />
			</td>
			<td valign="middle" align="right">
				<button onclick="nextDate('next');" style="width:10px"><img src="images/mright.gif" /></button>
			</td>
			
		</tr>
	</table>	

	<div id="result" rpc_excel_panel="off"></div>

</form>

<script>
	getResult();
</script>