{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
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

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="sFile" id="sFile" value="">
	<div class="page_caption">Изпращане на фактури по имейл</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter">
		<tr>
			<td>{include file="finance_instruments_tabs.tpl"}</td>
		</tr>
	</table>

	<p>Съдържание на ftp://213.91.252.137/</p>

	<table class="page_data">
		<tr>
			<td colspan="2" align="left">
					
					<table class="search" >

						<tr>
							<td>
								<button name="Send" id="b100" title="Разпрати" type="button" class="ui-final-action" onclick="send_data()" ><span class="ui-icon ui-icon-check" aria-hidden="true"></span>Разпрати</button>
							</td>
							<td>
								<button name="Settings" id="b100" title="Настройки" type="button" class="ui-final-action" onclick="settings()" ><span class="ui-icon ui-icon-settings" aria-hidden="true"></span>Настройки</button>
							</td>
							<td>
								<button name="Refresh" id="b100" title="Опресни" type="button" class="ui-final-action" onclick="onInit()" ><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span>Опресни</button>
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
