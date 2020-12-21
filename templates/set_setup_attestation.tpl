{literal}
	<script>
		rpc_debug=true;
		
		function onTypeChange()
		{
			var myValuation = document.getElementById('valuation');
			
			if( document.getElementById('type').value == 'констатация' )
			{
				myValuation.disabled = '';
				$('percent').disabled = 'disabled';
			}
			else
			{
				myValuation.disabled = 'disabled';
				myValuation.value = '';
				$('percent').disabled = '';
			}
		}
	
		function validatePercent()
		{
			percent	= $('percent').value;
			type	= $('type').value;
			
			if( type == 'поощрение' && (percent != '' && percent != 0 && percent < 100 ) )
			{
				$('percent').focus();
				alert('Поощрението трябва да е по-голямо от 100%!');
			}
			if( type == 'наказание' && (percent != '' && percent != 0 && percent > 100 ) )
			{	 
				$('percent').focus();
				alert('Наказание трябва да е по-малко или равно на 100%!');
			}
		}
		
		
		var my_action = '';
		
	 
		
		
		
	</script>
{/literal}

<dlcalendar click_element_id="img_date_start" input_element_id="date_start" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_end" input_element_id="date_end" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 2 );">
		<input type="hidden" id="id" name="id" value="{$nID}">
		<input type="hidden" id="id_person" name="id_person" value="{$nIDPerson}">
		<input type="hidden" id="first_day_of_month" name="first_day_of_month" value="{$first_day_of_month}" />
		
		<div class="page_caption">{if $id}Редактиране на атестация{else}Нова атестация{/if}</div>
		
		<table class="input">
			<tr class="odd">
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr class="odd">
				<td width="150">Тип:</td>
				<td>
					<select class="default" id="type" name="type" onchange="onTypeChange();">
						<option value="поощрение">поощрение</option>
						<option value="наказание">наказание</option>
						<option value="констатация">констатация</option>
					</select>
				</td>
			</tr>
			<tr class="even">
				<td>Оценка:</td>
				<td><input id="valuation" name="valuation" type="text" class="default" />
				
				Процент:&nbsp;<input id="percent" name="percent" type="text" class="default" maxlength="3" style="width:30px;" onkeypress="formatDigits(event);" onblur="validatePercent();"/>&nbsp;%</td>
			</tr>
			<tr>
				<td> Дата от: </td>
				<td>	
					<input type="text"  id="date_start" name="date_start" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ"  />	
					&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_start" />	
				</td>
			</tr>
			<tr>
				<td> Дата до: </td>
				<td>	
					<input type="text"  id="date_end" name="date_end"/>		
					&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_end" />	
				</td>
			</tr>
			<tr class="odd">
				<td>Становище:</td>
				<td colspan="4">
					<textarea cols="40" rows="4" id="attitude" name="attitude"></textarea>
				</td>
			</tr>
			<tr class="odd">
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		
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

{literal}
	<script>

		if( $('id').value == 0 )
		{
			$('date_start').value = $('first_day_of_month').value;
			
		}
		
		onTypeChange();
		
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