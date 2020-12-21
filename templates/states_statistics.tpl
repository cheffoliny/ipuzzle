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

<form action="" name="form1" id="form1" onsubmit="return false">
	

	<div class="page_caption">Наличности - Статистика</div>

	<br>
	
	<center>
	
	<table class="input" align="center" style="width:900px;" border="0">
		<tr align="center">
			<td>
				Филтър
			</td>
			<td align="left">
				<select name="nIDFilter" id="nIDFilter" />
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