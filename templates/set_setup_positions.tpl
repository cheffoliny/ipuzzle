<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 3 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		
		<div class="page_caption">{if $id}Редактиране на Длъжност{else}Нова Длъжност{/if}</div>
		<table class="input">
			<tr class="odd">
				<td width="80">Код:</td>
				<td><input type="text" name="code" id="code" style="width: 80px;" onkeypress="return formatDigits(event);" /></td>
			</tr>
			<tr class="even">
				<td>Функция:</td>
				<td>
					<select name="sFunction" id="sFunction" class="select100">
						<option value="none">-- избери --</option>
						<option value="technic">техническа</option>
						<option value="dispatcher">диспечерска</option>
						<option value="patrul">патрул</option>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td>Длъжност:</td>
				<td><input type="text" name="name" id="name" style="width: 283px;" /></td>
			</tr>
		</table>

		<table class="input">
			<tr class="odd">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
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