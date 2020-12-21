{literal}
	<script>
		rpc_debug = true;
		
		function update()
		{
			loadXMLDoc2( 'save', 3 );
			return false;
		}
	</script>
{/literal}

<dlcalendar click_element_id="editDate" input_element_id="sDate" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return update();">
		
		<div class="page_caption">Форма Приключване</div>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td align="left">Дата:&nbsp;</td>
				<td align="left">
					<input type="text" name="sDate" id="sDate" class="inp100" onkeypress="return formatDate( event, '.' );" />
					&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editDate" />
				</td>
			</tr>
			<tr class="even">
				<td align="left">Касов Апарат:&nbsp;</td>
				<td align="left">
					<select class="select200" name="nIDPayDesk" id="nIDPayDesk" />
				</td>
			</tr>
			<tr class="odd">
				<td align="left">Сума Оборот:</td>
				<td align="left">
					<input type="text" class="inp50" id="nOborot" name="nOborot" onkeypress="return formatMoney( event );" />&nbsp;лв.
				</td>
			</tr>
			<tr class="even">
				<td align="left">Сума Сторно:</td>
				<td align="left">
					<input type="text" class="inp50" id="nStorno" name="nStorno" onkeypress="return formatMoney( event );" />&nbsp;лв.
				</td>
			</tr>
		</table>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2( 'init' );
</script>