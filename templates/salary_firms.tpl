

	<script>
		
		rpc_debug = true;

		{literal}
		function onInit() {
		{/literal}	
		
			loadXMLDoc2('load');
			
			{if $nIDSelectFirmFrom} 
			
			{literal}
			rpc_on_exit = function() {
				loadXMLDoc2('result');
				rpc_on_exit = function() {};
			}
			{/literal}
			{/if}
	{literal}
		}
		
		function getResult(type) {
			if($('sfield'))	$('sfield').value = "";
			$('sAct').value = type;
			loadXMLDoc2('result');
		}
		
		function personnel( id ) {
			dialogPerson( id );
		}
		
		function openSalary(id) {
			var sMonth,sYear;
			
			sMonth = $('month').value;
			sYear  = $('year').value;
			
			dialogPersonSalary(id,sMonth,sYear);
		}
	{/literal}		
	</script>


<form action="" name="form1" id="form1">
	<input type="hidden" id="nIDSelectFirmFrom" name="nIDSelectFirmFrom"	value="{$nIDSelectFirmFrom}"/>
	<input type="hidden" id="nIDSelectFirmTo" 	name="nIDSelectFirmTo" 		value="{$nIDSelectFirmTo}"/>
	<input type="hidden" id="sAct" name="sAct" value="1">
	<table class = "page_data">
		<tr>
			<td class="page_name">Работни заплати - По фирми (Подробна) </td>

		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
					
				
				<td align="right">Служители от:</td>
				<td>
					<select name="nIDFirmFrom" id="nIDFirmFrom" class="select150"/>
				</td>
				
				<td style="width:50px;">
				&nbsp;
				</td>
				<td align="right">За сметка на:</td>
				<td>
					<select name="nIDFirmTo" id="nIDFirmTo" class="select150" />
				</td>
				
				<td style="width:50px;">
				&nbsp;
				</td>
				
				<td align="center">
					Год
					<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
				
				<td style="width:150px;">
					<button type="button" onClick="getResult(1);" name="Button" id="button1"><img src="images/confirm.gif">Служители</button>
				</td>
				<td>
					<button type="button" onClick="getResult(2);" name="Button" id="button2"><img src="images/confirm.gif">Региони</button>
				</td>
			</tr>
		</table>
		
	</center>

	<hr>
	
	<div id="result"></div>

</form>

<script>
	onInit();
</script>
