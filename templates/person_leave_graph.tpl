{literal}
	<script>
		rpc_debug = true;
		rpc_autonumber = false;
		
		function setApplication( id, id_person )
		{
			dialogSetupPersonLeave( id, id_person );
		}
		
		function setHospital( id, id_person )
		{
			dialogSetHospital( id, id_person );
		}
		
		function openPerson( id )
		{
			dialogPerson( id + "&enable_refresh=0" );
		}
		
		function rpcEnd( oCallerHandle )
		{
			rpc_on_exit = function()
			{
				if( oCallerHandle )oCallerHandle.focus();
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( "result" );
		}
	</script>
	
	<style>
		table.legend
		{
			width: 280px;
			height: 120px;
			border-width: 1px;
			border-spacing: 1px;
			border-style: outset;
			border-color: gray;
			border-collapse: separate;
		}
		table.legend td
		{
			border-width: 5px;
			padding: 0px;
			border-style: double;
			border-color: gray;
			font-size: 12px;
		}
	</style>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<table class = "page_data">
		<tr>
			<td class="page_name">Персонал - Отпуски</td>
		</tr>
	</table>
	
	<center>
		<table class="input">
			<tr>
				<td>
					<div id="legend">
						<center>
							<div>Легенда:</div>
							<table class="legend">
								<tr>
									<td width="100px;">&nbsp;</td>
									<td align="center" width="90px;" style="font-weight: bold;">Платени</td>
									<td align="center" width="90px;" style="font-weight: bold;">Неплатени</td>
								</tr>
								<tr>
									<td align="center" style="font-weight: bold;">Потвърдени</td>
									<td style="background-color: #64FF64;">&nbsp;</td>
									<td style="background-color: #FF6464;">&nbsp;</td>
								</tr>
								<tr>
									<td align="center" style="font-weight: bold;">Непотвърдени</td>
									<td colspan="2" style="background-color: #FF9632;">&nbsp;</td>
								</tr>
								<tr>
									<td align="center" style="font-weight: bold;">Неразрешени</td>
									<td colspan="2" style="background-color: #000000;">&nbsp;</td>
								</tr>
								<tr>
									<td align="center" style="font-weight: bold;">Болнични</td>
									<td colspan="2" style="background-color: #DC64FF;">&nbsp;</td>
								</tr>
							</table>
						</center>
					</div>
				</td>
				<td>
					<table class="input" style="width: 705px;">
						<tr>
							<td align="right">Тип:&nbsp;</td>
							<td align="left">
								<select id="sResultType" name="sResultType" class="select150">
									<option value="application">Отпуски</option>
									<option value="hospital">Болнични</option>
								</select>
							</td>
							
							<td style="width: 10px;">&nbsp;</td>
							
							<td align="right">Фирма:&nbsp;</td>
							<td align="left">
								<select id="nIDFirm" name="nIDFirm" class="select200" onchange="loadXMLDoc2( 'loadOffices' )" />
							</td>
							
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td align="right">Регион:&nbsp;</td>
							<td align="left">
								<select id="nIDOffice" name="nIDOffice" class="select200" onchange="loadXMLDoc2( 'loadObjects' )" />
							</td>
							
							<td style="width: 10px;">&nbsp;</td>
							
							<td align="right">Обект:&nbsp;</td>
							<td align="left" colspan="2">
								<select id="nIDObject" name="nIDObject" style="width: 350px;" />
							</td>
						</tr>
						<tr>
							<td align="right">Длъжности:&nbsp;</td>
							<td align="left">
								<select name="nIDPosition" id="nIDPosition" style="width: 200px;"></select>
							</td>
							
							<td style="width: 10px;">&nbsp;</td>
							
							<td align="right">Месец:&nbsp;</td>
							<td align="left">
								<select id="sDate" name="sDate" class="select200" />
							</td>
							
							<td align="right">
								<button id="btnSearch" onClick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif">Търси</button>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</center>
	
	<hr>
	
	<div id="result" rpc_paging="off" rpc_excel_panel="off"></div>
</form>

{literal}
	<script>
		rpc_on_exit = function()
		{
			rpc_on_exit = function() {}
			
			loadXMLDoc2( "result" );
		}
		
		loadXMLDoc2( 'load' );
	</script>
{/literal}