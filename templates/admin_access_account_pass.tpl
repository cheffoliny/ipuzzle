{literal}
<script>
	//rpc_debug=true;
</script>
{/literal}

<form id="form1" onSubmit="return loadXMLDoc('update', 3);">
	<input type="hidden" name="id" id="id" value="{$data.id|default:0}" />
	<div class="page_caption">Промяна на парола</div>
	
	<div id="search">
	<fieldset>
	<legend>Промяна на парола</legend>
		<table  class="input">
			<tr class="odd" style="height: 5px;"><td colspan="2"></td></tr>
			<tr class="even">
				<td align="left">Име</td>
				<td><input type="text" name="name" id="name" style="width: 200px;" value="{$data.name|escape:"html"}" disabled /></td>
			</tr>
			<tr class="even">
				<td align="left">Потр. име</td>
				<td><input type="text" name="username" id="username" style="width: 200px;" value="{$data.username|escape:"html"}" disabled /></td>
			</tr>
			<tr class="even">
				<td align="left">Нова Парола</td>
				<td><input type="password" name="password" id="password" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="even">
				<td align="left">Повтори Паролата</td>
				<td><input type="password" name="confirm_password" id="confirm_password" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="odd" style="height: 5px;"><td colspan="2"></td></tr>
		</table>
	</fieldset>
	</div>

	<div id="search">
		<table width="100%" cellspacing="5px">
			<tr><td align="right" valign="bottom">
				<button type="submit" class="search"> Запиши </button>
				<button onClick="parent.window.close();"> Затвори </button>
			</td></tr>
		</table>
	</div>
</form>

<script>
	//loadXMLDoc('result');	
</script>