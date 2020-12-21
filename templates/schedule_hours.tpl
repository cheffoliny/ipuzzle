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

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	<input type="hidden" name="nIDFirm2" id="nIDFirm2" value="{$nIDFirm}" />
	<input type="hidden" name="nIDOffice2" id="nIDOffice2" value="{$nIDOffice}" />
	<input type="hidden" name="nIDObject2" id="nIDObject2" value="{$nIDObject}" />
	
	<div class="page_caption">Отработени часове</div>
	
	<center>
		<table class="search" cellspacing="3">
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
					<button type="button" onClick="return loadXMLDoc2('result');" name="Button"><img src="images/new_win.gif">Покажи</button>
				</td>
			
			</tr>
	  	</table>
	</center>
		
	<hr>
	
	<div id="result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="off" style="width: 900px; height: 370px; overflow: auto;"></div>
	
	<hr />
	
	<div id="search"  style="padding-top: 10px;width: 900px;">
		<table width="100%" cellspacing="1px" >
			<tr valign="top">

				<td valign="bottom" align="right" width="750px">
					<a href="#" onclick="onPrint('export_to_xls');"><img src="images/excel.gif" border="0" title="Експорт към EXCEL" /></a>
				</td>

				<td valign="bottom" align="center" width="50px">
					<a href="#" onclick="onPrint('export_to_pdf');"><img src="images/pdf2.gif" border="0" title="Експорт към PDF" /></a>
				</td>
				
				<td valign="top" align="right" width="100px">
					<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
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