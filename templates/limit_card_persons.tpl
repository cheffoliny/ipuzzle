{literal}
	<script>
		rpc_debug = true;
		
		function editLimitCardPersons(id) {
			var lc = document.getElementById('nID').value;
			
			try {
				var idLc = id.split(',');
				dialogLimitCardPersons(idLc[0], lc);
			} catch (e) {
				alert(e.description);
			}
		}
		
		function openPerson(id) {
			try {
				var idLc = id.split(',');
				dialogPerson(idLc[1]);
			} catch (e) {
				alert(e.description);
			}
		}
		
		
		function delLimitCardPerson(id) {
			var idLc = id.split(',');
			if ( document.getElementById('lock').value != 'closed' ) {
				if ( confirm('Наистина ли желаете да премахнете служитела?') ) {
					document.getElementById('nIDPerson').value = idLc[0];
					loadXMLDoc2('delete', 0);
					rpc_on_exit = function() {
						test();
						document.getElementById('nIDPerson').value = 0;
						rpc_on_exit = function() {};
					}
				}
			}
		}
		
		function test() {
			rpc_result_area='result'
			loadXMLDoc2('result');
			
			rpc_on_exit = function() {
				rpc_result_area = 'dresult';

				rpc_method = 'post';
				rpc_xsl = 'xsl/limit_card_persons.xsl';
				rpc_html_debug = true;
				
				loadXMLDoc2('result2');
				rpc_result_area = 'result';
				rpc_xsl = 'xsl/general_result.xsl';
				rpc_method = 'get';
				
				rpc_on_exit = function() {};
			}	
		}
		
		function formClose() {
			opener.loadXMLDoc2('result');
			window.close();
		}
	</script>

	<style>
	
		table { 
			empty-cells: show !important; 
		}

		table td { 
			empty-cells: show !important; 
		}

		table.result {
			empty-cells: show !important;
			border-collapse: collapse !important;
			margin-bottom: 20px;
		}

		table.result th {
			padding: 2px 7px 2px 7px;
		}

		table.result tr {
			height: 24px;
		}

		table.result td {
			white-space: nowrap !important;
		}
		
		td.test {
			background-color:#eff465;
		}
		

	</style>
{/literal}

<div>
	<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />
	<input type="hidden" id="lock" name="lock" value="{$lock}" />

	<div class="page_caption">Служители към лимитна карта № {$nNum}</div>

	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
	<tr>
		<td>{include file=limit_card_tabs.tpl}</td>
	</tr>
	<tr>
		<td id="filter_result">
			<!-- начало на работната част -->
			<center>
				<table class="search">
					<tr>
						<td valign="top" align="right" style="width: 700px;">
							<button id="b100" onClick="editLimitCardPersons('0,0');"><img src="images/plus.gif" />Добави</button>
						</td>
					</tr>

			</table>
			</center>

			<hr>
			
			<div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width:700px; height:175px; overflow-x: auto; overflow-y: auto;"></div>
		</td>
	</tr>
	<tr>
		<td >		
			<div id="dresult"  rpc_excel_panel="off" rpc_paging="off"  style="width:700px; height:175px; overflow: none;"></div>
		</td>
 		<!-- край на работната част -->
	</tr>
	</table>

	<div id="search"  style="padding-top: 10px; width:700px;">
		<table  class="input">
			<tr valign="top" class="odd">
				<td valign="top" align="right" width="800px">
					<button id="b100" onClick="formClose();"><img src="images/cancel.gif" />Затвори</button>
				</td>
				
			</tr>
		</table>
	</div>
	<div id="NoDisplay" style="display:none"></div>
	</form>
</div>

{literal}
<script>
	loadXMLDoc2('result');
	
	rpc_on_exit = function() {
		//rpc_prefix = 'dresult';
		rpc_result_area = 'dresult';

		//rpc_debug = true;
		rpc_method = 'post';
		rpc_xsl = 'xsl/limit_card_persons.xsl';
		rpc_html_debug = true;
		
		loadXMLDoc2('result2');
		rpc_prefix = '';
		rpc_result_area = 'result';
		rpc_xsl = 'xsl/general_result.xsl';
		rpc_method = 'get';
		
		rpc_on_exit = function() {};
	}
</script>
{/literal}

{if $lock eq 'closed' || $lock eq 'cancel'}
	{literal}
	<script>
		if( form = document.getElementById('form1') ) {
			for( i = 0; i < form.elements.length - 1; i++ ) form.elements[i].setAttribute('disabled', 'disabled');
		}
	</script>
	{/literal}
{/if}
