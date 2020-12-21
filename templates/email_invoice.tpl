{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function send_data() {
			loadXMLDoc2('send');
		}
		
		function settings() {
			dialogSetInvoiceMailScheme();
		}
		
//		function resize() {
//			var div = document.getElementById('result');
//			div.style.height = document.body.offsetHeight-140;
//		}
	</script>
	

{/literal}

<dlcalendar click_element_id="imgPeriodFrom" input_element_id="sPeriodFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgPeriodTo" input_element_id="sPeriodTo" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="sFile" id="sFile" value="">
	<div class="page_caption">Изпращане на фактури по имейл</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter">
		<tr>
			<td>{include file=finance_instruments_tabs.tpl}</td>
		</tr>
	</table>

	<p>Съдържание на ftp://213.91.252.137/</p>

	<table class="page_data">
		<tr>
			<td colspan="2" align="left">
					
					<table class="search" >

						<tr>
							<td>
								<button name="Send" id="b100" name="b100" title="Разпрати" type="button" onclick="send_data()" ><img src="images/confirm.gif" />Разпрати</button>
							</td>
							<td>
								<button name="Settings" id="b100" name="b100" title="Настройки" type="button" onclick="settings()" ><img src="images/setup.gif" />Настройки</button>
							</td>
							<td>
								<button name="Refresh" id="b100" name="b100" title="Опресни" type="button" onclick="onInit()" ><img src="images/refresh_ppp.gif" />Опресни</button>
							</td>
						</tr>

				</table>

			</td>
			
		</tr>
		
	</table>
	
	<hr>
	<div id="result" rpc_excel_panel="on" rpc_paging="on" rpc_resize="on" style="overflow: auto;" ></div>

</form>

{literal}
	<script>
		//resize();
		
		onInit();
	</script>
{/literal}