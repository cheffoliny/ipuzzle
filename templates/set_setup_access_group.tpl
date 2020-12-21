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

<form action="" id="form1" onSubmit="return false;">
	<input type=hidden name=id value="{$id|default:0}">

	<div id="builder">
		<table class="input">
			<tr>
				<td align="right">Наименование</td>
				<td><input type="text" name="name" id="name" size=33 /></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		</table>
	</div>

	<div id="search">
		<table width="100%" cellspacing=5px>
			<tr><td align="right" valign="bottom">
				<button type=submit id=b100 onclick="return update(); return false;"><img src=images/confirm.gif />Потвърди</button>&nbsp;
				<button id="b100" onClick="parent.window.close()"><img src="images/cancel.gif" />Затвори</button>
			</td></tr>
		</table>
	</div>

</form>

<script>
	loadXMLDoc('result');
</script>