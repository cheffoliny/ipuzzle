{literal}

	<script>
		
		rpc_debug = true;
	
		function getResult() {
			loadXMLDoc2('result');
		}
		function openDetailed(nIDFirmFrom,nIDFirmTo) {
			var sMonth,sYear;
			var params = new Array();
			sMonth 	= $('month').value;
			sYear 	= $('year').value;
			
			dialogSalaryFirms(sMonth,sYear,nIDFirmFrom,nIDFirmTo);
			
			//window.location.href = 'page.php?page=salary_firms&sMonth='+sMonth+'&sYear='+sYear+'&nIDFirmFrom='+nIDFirmFrom+'&nIDFirmTo='+nIDFirmTo;
			
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-salary-report ui-salary-firms-total-report" onsubmit="return false;">
	
	<table class="page_data ui-salary-report-heading">
		<tr>
			<td class="page_name">Работни заплати - По фирми (Обобщена) </td>

		</tr>
	</table>
	
	<center>
		<table class="search ui-salary-legacy-filter">
			<tr>
						
				<td align="center">
					Год
					<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
				
				<td>
					<button type="button" class="btn btn-sm btn-primary" onClick="getResult();" name="Button" id="button1"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Покажи</button>
				</td>
			</tr>
		</table>
		
	</center>

	<hr>
	
	<div id="result" class="ui-salary-report-result" rpc_autonumber="off"></div>

</form>
