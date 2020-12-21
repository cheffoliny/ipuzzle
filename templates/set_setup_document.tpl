{literal}
<script>
	rpc_debug=true;
	
	var my_action = '';
	
	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'document' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestDocument );
			}			
		}
	}
		
	function onSuggestDocument ( aParams ) {
		$('id_document').value = aParams.KEY;
	}
	
	function docChange() {
		$('id_document').value = 0;
	}
	
	function form_submit() {
		frm = document.getElementById('valid_from').value;
		to = document.getElementById('valid_to').value;
		if ( frm == '' || to == '' ) {
			if ( confirm('Няма въведена валидност! Искате ли да остане така?') ) {
				my_action = 'save'; 
				return loadXMLDoc('save', 3); //3
			}
		} else return loadXMLDoc('save', 3); //3
	}
	
</script>
{/literal}

<dlcalendar click_element_id="img_date_in" input_element_id="date_in" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_valid_from" input_element_id="valid_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_valid_to" input_element_id="valid_to" tool_tip="Изберете дата"></dlcalendar>

<div class="content">
	<form action="" method="POST" name="form1" onsubmit="form_submit(); return false;">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="id_person" name="id_person" value="{$id_person}">
		<input type="hidden" id="id_document" name="id_document" value="0">
		
		<div class="page_caption">{if $id}Редактиране на документ{else}Нов документ{/if}</div>

		<fieldset>
			<legend>Съпътващи документи</legend>
			<table class="input">
				<tr class="odd">
					<td colspan="2" style="width: 5px;"></td>
				</tr>
				<tr class="even">
					<td style="width: 110px;" nowrap>Тип на документа:</td>
					<td><input id="document" name="document" type="text" style="width: 272px;" suggest="suggest" queryType="document" onChange="docChange();" /></td>
				</tr>				
				<tr class="odd">
					<td>Дата на издаване:</td>
					<td>
						<input name="date_in" id="date_in" type="text" style="width: 104px;" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
						&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_in" />
						&nbsp;&nbsp;№ : &nbsp;<input name="doc_num" id="doc_num" type="text" style="width: 80px;" />
					</td>
				</tr>
				<tr class="even">
					<td>Валидност:</td>
					<td><table class="input"><tr class="even">
						<td>от:</td>
						<td>
							<input name="valid_from" id="valid_from" type="text" style="width: 80px;" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_valid_from" />											
						</td>
						<td>до:</td>
						<td>
							<input name="valid_to" id="valid_to" type="text" style="width: 80px;" onKeyPress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />
							&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_valid_to" />											
						</td>
					</tr></table></td>
				</tr>
				<tr class="odd">
					<td colspan="2" style="width: 5px;"></td>
				</tr>
			</table>
		</fieldset>

		<fieldset>
			<legend>Допълнителна информация</legend>
			<table class="input">
				<tr class="odd">
					<td style="width: 5px;"></td>
				</tr>
				<tr class="even">
					<td align="center">
						<textarea name="note" style="width: 395px; height: 60px;" id="note"></textarea>
					</td>
				</tr>
				<tr class="odd">
					<td style="width: 5px;"></td>
				</tr>
			</table>
		</fieldset>
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