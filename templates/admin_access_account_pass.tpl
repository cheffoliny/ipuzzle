{literal}
<script>
	//rpc_debug=true;
</script>
{/literal}

<form id="form1" class="ui-access-dialog ui-access-password-dialog" onSubmit="return loadXMLDoc('update', 3);">
	<input type="hidden" name="id" id="id" value="{$data.id|default:0}" />
	<div class="page_caption">Промяна на парола</div>
	
	<div id="search" class="ui-access-dialog-body">
	<fieldset class="ui-access-selection">
	<legend>Промяна на парола</legend>
		<table class="input ui-access-form-table">
			<tr class="odd" style="height: 5px;"><td colspan="2"></td></tr>
			<tr class="even">
				<td align="left">Име</td>
				<td><input type="text" name="name" id="name" class="form-control" style="width: 200px;" value="{$data.name|escape:"html"}" disabled /></td>
			</tr>
			<tr class="even">
				<td align="left">Потр. име</td>
				<td><input type="text" name="username" id="username" class="form-control" style="width: 200px;" value="{$data.username|escape:"html"}" disabled /></td>
			</tr>
			<tr class="even">
				<td align="left">Нова Парола</td>
				<td><input type="password" name="password" id="password" class="form-control" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="even">
				<td align="left">Повтори Паролата</td>
				<td><input type="password" name="confirm_password" id="confirm_password" class="form-control" style="width: 150px;" value="" /></td>
			</tr>
			<tr class="odd" style="height: 5px;"><td colspan="2"></td></tr>
		</table>
	</fieldset>
	</div>

	<div id="search" class="ui-access-dialog-actions">
		<table width="100%" cellspacing="5px">
			<tr><td align="right" valign="bottom">
				<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
				<button onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
			</td></tr>
		</table>
	</div>
</form>

<script>
	//loadXMLDoc('result');	
</script>
