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



<form action="" name="form1" id="form1" onSubmit="return false;">
	<div class="page_caption">Фишове</div>
	
	<table class="search" style="width:100%;">
		<tr>
			<td align="right">
				<button onclick="editTicket();"><img src="images/plus.gif">Импортиране</button>
			</td>
		</tr>
	</table>
	
	<center>
	
			
	</center>
	
	<hr>
	<div id="result"></div>

</form>


{literal}
	<script>
		onInit();
	</script>
{/literal}