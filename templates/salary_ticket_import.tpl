{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
	
		function editTicket() {
			dialogSetSalaryTicketImport();
		}
		
	</script>
{/literal}



<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-salary-report ui-salary-ticket-list" onSubmit="return false;">
	<div class="page_caption">Фишове</div>
	
	<table class="search ui-salary-ticket-toolbar">
		<tr>
			<td align="right">
				<button type="button" class="btn btn-sm btn-success" onclick="editTicket();"><span class="ui-icon ui-icon-file-import" aria-hidden="true"></span> Импортиране</button>
			</td>
		</tr>
	</table>
	
	<center>
	
			
	</center>
	
	<div id="result" class="ui-salary-report-result"></div>

</form>


{literal}
	<script>
		onInit();
	</script>
{/literal}
