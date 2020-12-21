<script>
{literal}
	rpc_debug = true;
	
	function openPPP( id )
	{
		var params = 'id=' + id;
		dialogPPP2( params );
	}
	
	function nullSentType()
	{
		document.getElementById('sSourceName').value = '';
	}
	
	function nullReceivedType()
	{
		document.getElementById('sDestName').value = '';
	}

{/literal}
</script>

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data">
		<tr>
			<td class="page_name">Приемо-предаване</td>
		</tr>
	</table>
	<hr />
	
	<div id="filter" style="width: 100%; text-align: center;">
		<table class="search">
			<tr>
				<td align="right">Тип Предаващ:&nbsp;</td>
				<td align="left" style="padding: 2px;">
					<select name="sSendType" id="sSendType" class="select250" onchange="nullSentType();">
						<option value="">--- Изберете ---</option>
						<option value="object">Обект</option>
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="client">Доставчик</option>
					</select>
				</td>
				
				<td width="50px">&nbsp;</td>
				
				<td align="right">Тип Получаващ:&nbsp;</td>
				<td align="left" style="padding: 2px;">
					<select name="sReceiveType" id="sReceiveType" class="select250" onchange="nullReceivedType();">
						<option value="">--- Изберете ---</option>
						<option value="object">Обект</option>
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="client">Доставчик</option>
					</select>
				</td>
				
				<td width="50px">&nbsp;</td>
				
				<td align="right">Статус:&nbsp;</td>
				<td align="left">
					<select name="sStatus" id="sStatus" class="select150">
						<option value="">--- Всички ---</option>
						<option value="confirm">Потвърдени</option>
						<option value="open">Непотвърдени</option>
						<option value="cancel">Анулирани</option>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right">Предаващ:&nbsp;</td>
				<td style="padding: 2px;">
					<input type="text" name="sSourceName" id="sSourceName" class="inp250" suggest="suggest" queryType="pppSourceName" queryParams="sSendType" />
				</td>
				
				<td width="50px">&nbsp;</td>
				
				<td align="right">Приемащ:&nbsp;</td>
				<td style="padding: 2px;">
					<input type="text" name="sDestName" id="sDestName" class="inp250" suggest="suggest" queryType="pppDestName" queryParams="sReceiveType" />
				</td>
				
				<td width="50px">&nbsp;</td>
				
				<td align="right">Номер:&nbsp;</td>
				<td align="left">
					<input type="text" name="nNumber" id="nNumber" class="inp150" onkeypress="return formatDigits(event);" />
				</td>
			</tr>
			
			<tr>
				<td align="right">От:&nbsp;</td>
				<td align="left" style="padding: 2px;">
					<div class="input-group" style="width: 250px;">
						<span class="input-group-addon">
						<img id="editFromDate" src="images/glyphicons/forw_right.png" style="width: 10px; height: 12px; cursor:pointer;" title="Начало на периода" /></span>
						<input type="text" name="sFromDate" id="sFromDate"	class="search-query" style="width:98px;" onkeypress="return formatDate(event, '.');" placeholder="От дата..." />
						<input type="text" name="sToDate"	id="sToDate"	class="search-query" style="width:98px;" onkeypress="return formatDate(event, '.');" placeholder="До дата..." />
						<span class="input-group-addon">
						<img id="editToDate" src="images/glyphicons/forw_left.png" style="width: 10px; height: 12px; cursor:pointer;" /></span>
					</div>
				</td>
				
				<td width="50px">&nbsp;</td>
				
				<td align="right">&nbsp;</td>
				<td align="left">
					
					&nbsp;
					
				</td>
				
				<td colspan="3" style="padding-left: 50px" align="right">
					<button class="btn btn-xs btn-success" onclick="openPPP( 0 );"><i class="fa fa-plus"></i> Добави </button>
					<button class="btn btn-xs btn-primaty" onclick="loadXMLDoc2( 'result' );" name="Button"><img src="images/glyphicons/search.png" style="width: 14px; height: 14px;" /> Търси </button>
				</td>
			</tr>
	  	</table>
	
	</div>
	
	<hr />
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2( 'setDefaults' );
</script>