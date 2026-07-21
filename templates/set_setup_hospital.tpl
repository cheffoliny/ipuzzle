<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<dlcalendar click_element_id="img_leave_from" input_element_id="leave_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_leave_to" input_element_id="leave_to" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date" input_element_id="date" tool_tip="Изберете дата"></dlcalendar>

<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-personnel-dialog ui-leave-entry-dialog" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 2 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="id_person" name="id_person" value="{$id_person|default:0}">
		
		<div class="page_caption">{if $id}Редактиране на молба за болничен{else}Нова молба за болничен{/if}</div>
		
		<fieldset class="ui-nomenclature-fieldset">
			<legend>Данни за болничния:</legend>

		<table class="input ui-nomenclature-form">
			<tr class="odd">
				<td>За година:</td>
				<td align="left"><input id="year" name="year" type="text" class="inp50" onkeypress="return formatDigits(event);" maxlength="4" value="{$year}" /></td>
				<td align="right">Дата:&nbsp;</td>
				<td align="left">
					<input id="date" name="date" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date}" />&nbsp;
					<button type="button" class="ui-inline-calendar-trigger" id="img_date" title="Изберете дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</td>
			</tr>
			<tr class="even">
				<td>От дата:</td>
				<td align="left">
					<input id="leave_from" name="leave_from" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<button type="button" class="ui-inline-calendar-trigger" id="img_leave_from" title="Изберете дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</td>
				<td align="right">До дата:&nbsp;</td>
				<td>
					<input id="leave_to" name="leave_to" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<button type="button" class="ui-inline-calendar-trigger" id="img_leave_to" title="Изберете дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</td>			
			</tr>
			<tr class="odd">
				<td>Брой работни дни:</td>
				<td><input id="application_days" name="application_days" type="text" class="inp50" onkeypress="return formatDigits( event );" maxlength="3" readonly /></td>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr style="height: 5px;"><td colspan="4"></td></tr>
		</table>
		</fieldset>
		
		<fieldset class="ui-nomenclature-fieldset">
			<legend>Допълнителна информация:</legend>
			<table class="input ui-personnel-notes">
				<tr class="odd">
					<td>
						<textarea id="info" name="info" style="width: 442px; height: 110px;"></textarea>
					</td>
				</tr>
				<tr style="height: 5px;"><td colspan="4"></td></tr>
			</table>
		</fieldset>
		
		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
</div>

{literal}
	<script>
		loadXMLDoc('result');
		
		rpc_on_exit = function( err ) {
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc('result');
				
				my_action = '';
			}
		}
	</script>
{/literal}
