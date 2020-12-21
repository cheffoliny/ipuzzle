{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		rpc_xls_debug = true;
		
		function openMonitor() {
			nCode = $('nObjectCode').value;
			
			dialogMonitor(nCode);
		}
		
		function loadTest() {
			loadXMLDoc2('test');
		}
				
	</script>
{/literal}


<form name="form1" id="form1" onsubmit="return false;">
	<div class="page_caption">Тестова страница!</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >

		<tr>
			<td id="filter_result">
			<!-- начало на работната част -->
			<center>
				<table class="search" style="width:100%;">
					<tr>
						<td valign="top" align="right">
							<table cellspacing="3" cellpadding="0">
								<tr>
									<td>
										<button id="b100" onClick="loadTest()">Тест</button>
									</td>
								
									<td>
										<input type="text" id="nObjectCode" name="nObjectCode" style="width: 100px; text-align: right;" maxlength="12" />
									</td>
									<td>
										<button id="b100" onClick="openMonitor()">Мониторинг</button>
									</td>
								</tr>
							</table>
						</td>
					</tr>
			  </table>
			</center>
		
			<hr>
					
		 	<!-- край на работната част -->
			</td>
		</tr>
	</table>


	<div id="result" rpc_resize="no" style="overflow: auto;"></div>

</form>
