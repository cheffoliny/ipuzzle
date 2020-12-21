<script>
	rpc_debug = true;
</script>

<dlcalendar click_element_id="imgEndTime" input_element_id="sEndTime" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgReasonTime" input_element_id="sReasonTime" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3);return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">Спиране движението на патрул</div>
		<br />

		<table class="input" border="0">
			<tr class="odd">
				<td align="right" style="width: 100px;">Пристигане:&nbsp;</td>
				<td>
					<input type="text" name="sEndTimeH" id="sEndTimeH" style="width: 60px;" onkeypress="return formatTimeS(event);" maxlength="8" title="ЧЧ:ММ:СС" />&nbsp;
					<input type="text" name="sEndTime" id="sEndTime" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ"  />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgEndTime" />
				</td>
			</tr>
			<tr>
				<td align="right" style="width: 100px;">Освобождаване:&nbsp;</td>
				<td>
					<input type="text" name="sReasonTimeH" id="sReasonTimeH" style="width: 60px;" onkeypress="return formatTimeS(event);" maxlength="8" title="ЧЧ:ММ:СС" />&nbsp;									
					<input type="text" name="sReasonTime" id="sReasonTime" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgReasonTime" />
				</td>
			</tr>
		</table>
		
		<fieldset>
			<legend>Бележка:</legend>
			<table class="input">
				<tr class="even">
					<td align="center">
						<textarea name="sNote" id="sNote" style="width: 270px; height: 50px;" /></textarea>
					</td>
				</tr>
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			</table>
		</fieldset>
		
		<br />
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>