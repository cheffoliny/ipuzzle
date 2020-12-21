{literal}
	<script>
	
		function update()
		{
			select_all_options('level_files');
			loadXMLDoc('update', 3);
			return false;
		}
		
	</script>
{/literal}

<div class="page_caption">{if $id>0}Редактиране на ниво{else}Ново ниво{/if}</div>

<form id="form1" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="{$id|default:0}">
	<input type="hidden" name="group" id="group" value="{$group|default:0}">

	<div id="builder">
		<table class="input">
			<tr>
				<td align="right">Наименование</td>
				<td><input type="text" name="name" id="name" size="53" /></td>
			</tr>
			<tr>
				<td align="right">Група</td>
				<td>
					<select name="id_group" id="id_group" class="select300">
					</select>
				</td>
			</tr>
			<tr>
				<td align="right">Описание</td>
				<td><textarea name="description" id="description" rows=2 cols=40></textarea></td>
			</tr>
			<tr>
				<td colspan=2 id=fieldset>
					<fieldset style="border: 1px solid black">
					<legend>Файлове</legend>
						<table>
							<tr>
								<td>
									<select name="files_all" id="all_files" style="width:360px" size="10" ondblclick="move_option_to( 'all_files', 'level_files', 'right');" multiple="multiple">
									</select>
								</td>
								<td>
									<button id=b25 name="button" title="Добави Файл" onClick="move_option_to( 'all_files', 'level_files', 'right' ); return false;"><img src=images/mright.gif /></button></br>
									<button id=b25 name="button" title="Премахни файл" onClick="move_option_to( 'all_files', 'level_files', 'left' ); return false;"><img src=images/mleft.gif /></button>
								</td>
								<td>
									<select name="level_files[]" id="level_files"  style="width:360px" size="10" ondblclick="move_option_to( 'all_files', 'level_files', 'left');" multiple="multiple">
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		</table>
	</div>

	<div id="search">
		<table width="100%" cellspacing=5px>
			<tr><td align="right" valign="bottom">
				<button id=b100 onclick="return update();"><img src=images/confirm.gif />Потвърди</button>&nbsp;
				<button id=b100 onClick="parent.window.close();"><img src="images/cancel.gif" />Затвори</button>
			</td></tr>
		</table>
	</div>

</form>

<script>
	loadXMLDoc('result');
</script>