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

<form action="" name="form1" id="form1" class="ui-monitor-report ui-states-statistics-report" onsubmit="return false">
	

	<div class="page_caption ui-monitor-heading">Наличности - Статистика</div>

	<br>
	
	<center>
	
	<table class="input ui-monitor-filter" align="center" style="width:900px;" border="0">
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
				<button type="button" id="editFromDate" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Дата от"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
			</td>	
			<td align="right">До:&nbsp;</td>
			<td align="left">
				<input type="text" name="sToDate" id="sToDate" class="inp100" onkeypress="return formatDate(event, '.');" />
				&nbsp;
				<button type="button" id="editToDate" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Дата до"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
			</td>
			<td class="ui-monitor-search-cell" align="right"><button type="button" name="Button" class="search" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
		</tr>
	</table>

	</center>
	<hr>
	<div id="result" class="ui-monitor-result"></div>
</form>

<script>
	onInit();
</script>
