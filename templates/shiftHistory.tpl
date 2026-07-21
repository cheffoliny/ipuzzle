{literal}
	<script>
		rpc_debug = true;
		
		function submit_form() {
			loadXMLDoc2( 'save', 0 );
		}
		
		function choiceSync(id) {
			document.getElementById('nIDSync').value = id;
			if ( confirm('Наистина ли желаете да синхронизирате обект с ID: {/literal}{$nID}{literal} с ID: '+id+'?') ) {
				loadXMLDoc2('sync', 2);
			}
		}		
	</script>
{/literal}


<form name="form1" id="form1" class="ui-nomenclature-dialog ui-schedule-dialog ui-shift-history-dialog" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />

	<div class="page_caption">Видове смени - ИСТОРИЯ</div>

	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >

	<tr>
		<td id="filter_result">
		<!-- начало на работната част -->
		<hr>
		
		<div id="result" class="ui-schedule-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width: 650px; height: 260px; overflow: auto;"></div>

 		<!-- край на работната част -->
		</td>
	</tr>
	</table>


	<div id="search" class="ui-schedule-actions-wrap" style="padding-top: 10px; width: 650px;">
		<table class="ui-nomenclature-actions ui-schedule-actions" width="100%" cellspacing="1px">
			<tr valign="top">
				<td valign="top" align="right" width="650px">
					<button type="button" id="b100" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
				</td>
			</tr>
		</table>
	</div>

</form>

<script>
	loadXMLDoc2('result');
</script>
