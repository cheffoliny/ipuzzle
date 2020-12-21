<script>
	//rpc_debug=true;
	
	var my_action = '';
</script>

<div class="content">
	<form action="" method="POST" name="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 2 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="id_person" name="id_person" value="{$id_person}">
		
		<div class="page_caption">{if $id}Редактиране на отпуска{else}Нова отпуска{/if}</div>

		<fieldset>
			<legend>Полагаем отпуск</legend>
			<table class="input">
				<tr class="odd">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="odd">
					<td width="150">Година:</td>
					<td><input id="year" name="year" type="text" value="{if !$id}{$year}{/if}" maxlength="4" style="width: 60px;" onkeypress="return formatDigits(event);" /></td>
				</tr>
				<tr class="even">
					<td>Брой дни:</td>
					<td><input id="due_days" name="due_days" type="text" maxlength="2" style="width: 60px;" onkeypress="return formatDigits(event);" />&nbsp;дена</td>
				</tr>
				<tr class="odd">
					<td colspan="2">&nbsp;</td>
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
		
		rpc_on_exit = function( err ) {
			if( my_action == 'save' && err == 0 ) {
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc('result');
				
				my_action = '';
			}
		}
	</script>
{/literal}
