<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<dlcalendar click_element_id="img_date" input_element_id="date" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" onsubmit="my_action = 'save'; return loadXMLDoc2( 'save', 2 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="id_person" name="id_person" value="{$id_person|default:0}">
		
		<div class="page_caption">{if $id}Редактиране на{else}Ново{/if} обезщетение</div>
		
		<fieldset>
			<legend>Данни за болничния:</legend>
		
		<table class="input">
			<tr class="odd">
				<td>За година:&nbsp;</td>
				<td align="left"><input id="year" name="year" type="text" class="inp50" onkeypress="return formatDigits( event );" maxlength="4" value="{$year}" /></td>
				<td align="right">Дата:&nbsp;</td>
				<td align="left">
					<input id="date" name="date" type="text" class="inp100" onKeyPress="return formatDate( event, '.' );" maxlength="10" title="ДД.ММ.ГГГГ" value="{$date}" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date" />
				</td>
			</tr>
			<tr class="even">
				<td>Месец:&nbsp;</td>
				<td align="left">
					<select id="nMonth" name="nMonth" class="select100">
						<option value="01">Януари</option>
						<option value="02">Февруари</option>
						<option value="03">Март</option>
						<option value="04">Април</option>
						<option value="05">Май</option>
						<option value="06">Юни</option>
						<option value="07">Юли</option>
						<option value="08">Август</option>
						<option value="09">Септември</option>
						<option value="10">Октомври</option>
						<option value="11">Ноември</option>
						<option value="12">Декември</option>
					</select>
				</td>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr class="odd">
				<td>Брой работни дни:</td>
				<td><input id="application_days" name="application_days" type="text" class="inp50" onkeypress="return formatDigits( event );" maxlength="3" /></td>
				<td colspan="2">&nbsp;</td>
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
		loadXMLDoc2( 'result' );
		
		rpc_on_exit = function( err )
		{
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc2( 'result' );
				
				my_action = '';
			}
		}
	</script>
{/literal}
