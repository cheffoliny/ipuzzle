{literal}
	<script>
		rpc_debug = true;
		
		function editScheduleSettings( id ) {
			dialogScheduleSettings( id );
		}
		
		
		function onChangeFirm() {
			loadXMLDoc2('getOffices');
		}
		
		function onChangeOffice() {
			loadXMLDoc2('getObjects');
		}
			
		function onPrint(type) {
			loadDirect(type);
		}		
	</script>
{/literal}

<form name="form1" id="form1" class="ui-nomenclature-list ui-schedule-report ui-schedule-hours-report" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	<input type="hidden" name="nIDFirm2" id="nIDFirm2" value="{$nIDFirm}" />
	<input type="hidden" name="nIDOffice2" id="nIDOffice2" value="{$nIDOffice}" />
	<input type="hidden" name="nIDObject2" id="nIDObject2" value="{$nIDObject}" />
	
	<div class="page_caption">Отработени часове</div>
	
	<center class="ui-nomenclature-filter-wrap ui-schedule-filter-wrap">
		<table class="search ui-nomenclature-filter ui-schedule-filter ui-schedule-hours-filter" cellspacing="3">
			<tr>
			
				<td align="right">Фирма</td>
				
				<td>
					<select id="nIDFirm" name="nIDFirm" onchange="onChangeFirm()" style="width: 150px" >
						<option value="0"> -- изберете -- </option>
					</select>
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион</td>
				
				<td>
					<select id="nIDOffice" name="nIDOffice" onchange="onChangeOffice()" style="width: 150px" >
						<option value="0"> -- изберете -- </option>
					</select>
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Обект</td>
				
				<td>
					<select id="nIDObject" name="nIDObject" style="width: 300px" >
						<option value="0"> -- изберете -- </option>
					</select>
				</td>

				<td>&nbsp;</td>
				
				<td align="right">
					<button type="button" onClick="return loadXMLDoc2('result');" name="Button"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Покажи</button>
				</td>
			
			</tr>
	  	</table>
	</center>
		
	<hr>
	
	<div id="result" class="ui-schedule-result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="off" style="width: 900px; height: 370px; overflow: auto;"></div>
	
	<hr />
	
	<div id="search" class="ui-schedule-actions-wrap" style="padding-top: 10px;width: 900px;">
		<table class="ui-nomenclature-actions ui-schedule-actions" width="100%" cellspacing="1px" >
			<tr valign="top">

				<td valign="bottom" align="right" width="750px">
					<button type="button" class="ui-schedule-export" title="Експорт към EXCEL" onclick="onPrint('export_to_xls');"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Excel</button>
				</td>

				<td valign="bottom" align="center" width="50px">
					<button type="button" class="ui-schedule-export" title="Експорт към PDF" onclick="onPrint('export_to_pdf');"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> PDF</button>
				</td>
				
				<td valign="top" align="right" width="100px">
					<button type="button" id="b100" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
				</td>
				
			</tr>
		</table>
	</div>	
	
</form>

{literal}
<script>
	loadXMLDoc2('init');
	
//	rpc_on_exit = function() {
//		
//		rpc_on_exit = function() {};
//	}
</script>
{/literal}
