<form id="form1" onSubmit="return loadXMLDoc( 'update', 3 );">
	
	<div class="page_caption">Промяна на парола{if $name} за {$name}{/if}</div>
	
	<div id="search">
	<fieldset>
	<legend>Промяна на парола</legend>
		<table  class="input">
			<tr class="odd" style="height: 5px;"><td colspan="2"></td></tr>
			<tr class="even">
				<td align="left">Стара парола</td>
				<td><input type="password" name="password" id="password" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="even">
				<td align="left">Нова парола</td>
				<td><input type="password" name="new_password" id="new_password" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="even">
				<td align="left">Повтори новата парола</td>
				<td><input type="password" name="confirm_password" id="confirm_password" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="odd" style="height: 5px;"><td colspan="2"></td></tr>
		</table>
	</fieldset>
	</div>
	
	<br />
	
	<div id="search">
		<table width="100%" cellspacing="5px">
			<tr><td align="right" valign="bottom">
				<button type="submit" class="search"> Запиши </button>
				<button onClick="parent.window.close();"> Затвори </button>
			</td></tr>
		</table>
	</div>
	
</form>