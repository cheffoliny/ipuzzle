{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function books_add() {
			//var id = $('nID').value;
			
			dialogBooksAdd(0);
		}
		
		function books_del(id) {
			dialogBooksDel(id);
		}		
		
		function books_set(id) {
			dialogBooksSet(id);
		}		

	</script>
	
	<style>
		.separator {
			width:10px;
		}
		
		.w120 {
			width:120px;
		}
		
	</style>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" name="sFile" id="sFile" value="">
	
	<div class="page_caption">Администрация на кочани</div>

	<table cellspacing="0" cellpadding="0" width="100%" id="filter">
		<tr>
			<td>{include file="finance_instruments_tabs.tpl"}</td>
		</tr>
	</table>

	<table class="page_data">
		<tr>
			<td colspan="2" align="center">
					<table class="search" >
					
						<tr>
							<td valign="top" align="right" >
								<button type="button" onClick="books_add();" title="Добави кочан" class="ui-final-action"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span>Добави</button>
							</td>
							
							<td valign="top" align="right" >
								<button type="button" class="search ui-final-action" onClick="books_set(0);" title="промени статус"><span class="ui-icon ui-icon-edit" aria-hidden="true"></span>Промени</button>
							</td>
						
							<td valign="top" align="right" >
								<button type="button" class="ui-final-action" onClick="books_del(0);" title="Изтрий кочан" style="background-color: red;"><span class="ui-icon ui-icon-delete" aria-hidden="true"></span>Изтрий</button>
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
