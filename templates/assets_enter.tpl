{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		
		function editAssetsPPP(id) {
			dialogAssetsPPP(id,'enter');
		}
		
	</script>
{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-assets-report ui-assets-movement-report" onSubmit="return false;">
	<div class="page_caption">Активи - Придобиване</div>
	
	<table class="search ui-assets-report-actions" style="width:100%;">
		<tr>
			<td align="right">
				<button type="button" onclick="editAssetsPPP( 0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Нов ППП </button>
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
	
		<table class="search ui-nomenclature-filter ui-assets-report-filter" border="0">
				<tr>
					<td align="right">От:&nbsp;</td>
					<td align="left">
						<input type="text" name="sFromDate" id="sFromDate" class="inp100" onkeypress="return formatDate(event, '.');" />
						&nbsp;
						<button type="button" id="editFromDate" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Изберете начална дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
					</td>
					
					<td width="50px">&nbsp;</td>
					
					<td align="right">До:&nbsp;</td>
					<td align="left">
						<input type="text" name="sToDate" id="sToDate" class="inp100" onkeypress="return formatDate(event, '.');" />
						&nbsp;
						<button type="button" id="editToDate" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Изберете крайна дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
					</td>
					<td class="ui-assets-search-cell" align="right"><button type="button" name="Button" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
				</tr>
		</table>
			
	</center>
	
	<hr>
	<div id="result"></div>

</form>


{literal}
	<script>
		onInit();
	</script>
{/literal}
