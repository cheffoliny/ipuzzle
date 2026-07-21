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
					rpc_on_exit = function(nCode) {
						rpc_on_exit = function() {};
						document.getElementById('nIDPerson').value = 0;
						if (parseInt(nCode, 10) === 0) {
							window.setTimeout(loadLimitCardReports, 0);
						}
					};
					loadXMLDoc2('delete', 0);
				}
			}
		}
		
		function loadLimitCardAvailability() {
			rpc_result_area = 'dresult';
			rpc_method = 'POST';
			rpc_xsl = 'xsl/limit_card_persons.xsl';
			rpc_html_debug = true;

			rpc_on_exit = function() {
				rpc_on_exit = function() {};
				rpc_result_area = 'result';
				rpc_xsl = 'xsl/general_result.xsl';
				rpc_method = 'POST';
				rpc_html_debug = false;
			};
			loadXMLDoc2('result2');
		}

		function loadLimitCardReports() {
			rpc_result_area = 'result';
			rpc_xsl = 'xsl/general_result.xsl';
			rpc_method = 'POST';
			rpc_html_debug = false;
			rpc_on_exit = function(nCode) {
				rpc_on_exit = function() {};
				if (parseInt(nCode, 10) === 0) {
					window.setTimeout(loadLimitCardAvailability, 0);
				}
			};
			loadXMLDoc2('result');
		}

		function test() {
			loadLimitCardReports();
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
	<form name="form1" id="form1" class="ui-nomenclature-dialog ui-technical-dialog ui-limit-card-persons" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />
	<input type="hidden" id="lock" name="lock" value="{$lock}" />

	<div class="page_caption">Служители към лимитна карта № {$nNum}</div>

	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
	<tr>
		<td>{include file="limit_card_tabs.tpl"}</td>
	</tr>
	<tr>
		<td id="filter_result">
			<!-- начало на работната част -->
			<center>
				<table class="search ui-technical-toolbar">
					<tr>
						<td valign="top" align="right" style="width: 700px;">
							<button type="button" id="b100" class="search" onClick="editLimitCardPersons('0,0');"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span>Добави</button>
						</td>
					</tr>

			</table>
			</center>

			<hr>
			
			<div id="result" class="ui-technical-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width:700px; height:175px; overflow-x: auto; overflow-y: auto;"></div>
		</td>
	</tr>
	<tr>
		<td >		
			<div id="dresult" class="ui-technical-result ui-limit-card-availability" rpc_excel_panel="off" rpc_paging="off" style="width:700px; height:175px; overflow: auto;"></div>
		</td>
 		<!-- край на работната част -->
	</tr>
	</table>

	<div id="search" class="ui-technical-actions-wrap" style="padding-top: 10px; width:700px;">
		<table class="input ui-nomenclature-actions ui-technical-actions">
			<tr valign="top" class="odd">
				<td valign="top" align="right" width="800px">
					<button type="button" id="b100" class="btn btn-xs btn-danger" onClick="formClose();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
				</td>
				
			</tr>
		</table>
	</div>
	<div id="NoDisplay" style="display:none"></div>
	</form>
</div>

{literal}
<script>
	loadLimitCardReports();
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
