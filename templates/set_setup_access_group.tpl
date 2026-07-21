{literal}
	<script>
		//rpc_debug=true;
	
		function update()
		{
			loadXMLDoc('update', 3);
			return false;
		}
	</script>
{/literal}

<div class="page_caption">{if $id}Редактиране на група{else}Нова група{/if}</div>

<form action="" id="form1" class="ui-access-dialog ui-access-group-dialog" onSubmit="return false;">
	<input type=hidden name=id value="{$id|default:0}">

	<div id="builder" class="ui-access-dialog-body">
		<table class="input ui-access-form-table">
			<tr>
				<td align="right">Наименование</td>
				<td><input type="text" name="name" id="name" class="form-control" size=33 /></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		</table>
	</div>

	<div id="search" class="ui-access-dialog-actions">
		<table width="100%" cellspacing=5px>
			<tr><td align="right" valign="bottom">
				<button type=submit id=b100 onclick="return update(); return false;"><span class="ui-icon ui-icon-check" aria-hidden="true"></span>Потвърди</button>&nbsp;
				<button id="b100" onClick="parent.window.close()"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
			</td></tr>
		</table>
	</div>

</form>

<script>
	loadXMLDoc('result');
</script>
