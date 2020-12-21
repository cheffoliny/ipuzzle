<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<dlcalendar click_element_id="img_leave_from" input_element_id="leave_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_leave_to" input_element_id="leave_to" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date" input_element_id="date" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 2 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="id_person" name="id_person" value="{$id_person|default:0}">
		
		<div class="page_caption">{if $id}Редактиране на молба за отпуск{else}Нова молба за отпуск{/if}</div>
		
		<fieldset>
			<legend>Данни за молбата:</legend>

		<table class="input">
			<tr class="odd">
				<td>За година:</td>
				<td align="left"><input id="year" name="year" type="text" class="inp50" onkeypress="return formatDigits(event);" maxlength="4" value="{$year}" /></td>
				<td align="right">Дата:&nbsp;</td>
				<td align="left">
					<input id="date" name="date" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date}" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date" />
				</td>
			</tr>
			<tr class="even">
				<td>От дата:</td>
				<td align="left">
					<input id="leave_from" name="leave_from" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_leave_from" />
				</td>
				<td align="right">До дата:&nbsp;</td>
				<td>
					<input id="leave_to" name="leave_to" type="text" class="inp100" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_leave_to" />
				</td>			
			</tr>
			<tr class="odd">
				<td>Брой работни дни:</td>
				<td><input id="application_days" name="application_days" type="text" class="inp50" onkeypress="return formatDigits(event);" maxlength="2" /></td>
				<td align="right">Тип:&nbsp;</td>
				<td>
					<select id="leave_types" name="leave_types" style="width: 126px;" >
						<option value="due">полагаем</option>
						<option value="unpaid">неплатен</option>
						<option value="student">пл. полагаем</option>
						<option value="quittance">обещетение</option>
						<option value="other">други</option>
					</select>
				</td>				
			</tr>
			<tr style="height: 5px;"><td colspan="4"></td></tr>
		</table>
		</fieldset>
		
		<fieldset>
			<legend>Допълнителна информация:</legend>
			<table class="input">
				<tr class="odd">
					<td>
						<textarea id="info" name="info" style="width: 442px; height: 110px;"></textarea>
					</td>
				</tr>
				<tr style="height: 5px;"><td colspan="4"></td></tr>
			</table>
		</fieldset>
		
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

{literal}
	<script>
		loadXMLDoc('result');
		
		rpc_on_exit = function( err )
		{
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc('result');
				
				my_action = '';
			}
		}
	</script>
{/literal}
