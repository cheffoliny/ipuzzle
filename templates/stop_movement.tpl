<script>
	rpc_debug = true;
</script>

<dlcalendar click_element_id="imgEndTime" input_element_id="sEndTime" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgReasonTime" input_element_id="sReasonTime" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-monitor-dialog ui-stop-movement-dialog" onsubmit="loadXMLDoc2('save', 3);return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption ui-monitor-heading">Спиране движението на патрул</div>
		<br />

		<table class="input ui-monitor-dialog-fields" border="0">
			<tr class="odd">
				<td align="right" style="width: 100px;">Пристигане:&nbsp;</td>
				<td>
					<input type="text" name="sEndTimeH" id="sEndTimeH" style="width: 60px;" onkeypress="return formatTimeS(event);" maxlength="8" title="ЧЧ:ММ:СС" />&nbsp;
					<input type="text" name="sEndTime" id="sEndTime" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ"  />&nbsp;
					<button type="button" id="imgEndTime" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Пристигане"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</td>
			</tr>
			<tr>
				<td align="right" style="width: 100px;">Освобождаване:&nbsp;</td>
				<td>
					<input type="text" name="sReasonTimeH" id="sReasonTimeH" style="width: 60px;" onkeypress="return formatTimeS(event);" maxlength="8" title="ЧЧ:ММ:СС" />&nbsp;									
					<input type="text" name="sReasonTime" id="sReasonTime" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<button type="button" id="imgReasonTime" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Освобождаване"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</td>
			</tr>
		</table>
		
		<fieldset class="ui-nomenclature-fieldset">
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
		<table class="input ui-monitor-dialog-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button type="button" class="btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>
