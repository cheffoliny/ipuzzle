{literal}

	<script>
		rpc_debug = true;
	
		function onInit() {
			loadXMLDoc2('load');
		}
		
	</script>

{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form id="form1" onsubmit="return false;">
	<table class="input">
		<tr>
			<td class="page_name">
				Статистики
			</td>
		</tr>	
	</table>
	<center>
	<table class="input" style="width:800px;">
		<tr>
			<td align="right">
				Филтър
			</td>
			<td>
				<select id="nIDFilter" name="nIDFilter"></select>
			</td>
			<td width="50px">&nbsp;</td>
			<td align="right">От:&nbsp;</td>
			<td align="left">
				<input type="text" name="sFromDate" id="sFromDate" class="inp100" onkeypress="return formatDate(event, '.');" />
				&nbsp;
				<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editFromDate" />
			</td>	
			<td align="right">До:&nbsp;</td>
			<td align="left">
				<input type="text" name="sToDate" id="sToDate" class="inp100" onkeypress="return formatDate(event, '.');" />
				&nbsp;
				<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editToDate" />
			</td>
			<td style="padding-left: 50px" align="right"><button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif">Търси</button></td>
		</tr>
	</table>
	</center>
	<hr>
	<div id="result"></div>
</form>



<script>
	onInit();
</script>