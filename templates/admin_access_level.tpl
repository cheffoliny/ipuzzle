{literal}
	<script>
		rpc_debug=true;
		
		function level_new(level_group)
		{
			group = level_group.value > 0 ? level_group.value : 0;
			dialog_win('set_setup_access_level&id=0&group='+group,850,430,0,'set_setup_access_level');
		}

		function level_edit(id)
		{
			dialog_win('set_setup_access_level&id='+id,850,430,0,'set_setup_access_level');
		}

		function level_delete(id)
		{
			if( confirm('Наистина ли желаете да премахнeте нивото?') )
			{
				document.getElementById('id').value=id;
				loadXMLDoc('delete_level');
			}
		}

		function group_delete(level_group)
		{
			if(level_group.value > 0)
				if( confirm('Наистина ли желаете да премахнeте групата?') )
				{
					document.getElementById('id').value=level_group.value;
					loadXMLDoc('delete_group');
				}
		}

		function group_update(level_group)
		{
			if(level_group.value > 0)
				dialog_win('set_setup_access_group&id='+level_group.value,400,140,0,'set_setup_access_group');

		}

	</script>
{/literal}

<form action="" id="form1" name="form1" onSubmit="return false;">
	<input type=hidden name="id" id="id"value="">

	<div id="search">
		<table class = "page_data">
			<tr>
				<td class="page_name">Номенклатури - НИВА НА ДОСТЪП</td>
				<td class="buttons">
					{if $right_edit}<button id="b70" onClick="return level_new(level_group)"><img src="images/plus.gif">Добави</button>
					{else}&nbsp;
					{/if}
				</td>
			</tr>
		</table>

		<center>
			<br />
			<table>
				<tr>
					<td>Група</td>
					<td>
						<select name="level_group"  id="level_group">
						</select>
						&nbsp;
						<button title='Търси' onclick="loadXMLDoc('result'); return false;" style='width:70px;'><img src='images/confirm.gif' alt='' />Търси</button>
						&nbsp;
						{if $right_edit}
							<button style="width: 30px" id=b25 title="Нова група" name="Button" onClick="dialog_win('set_setup_access_group&id=0',400,140,0,'set_setup_access_group')" ><img src="images/plus.gif" /></button>&nbsp;
							<button style="width: 30px" name="Button" id=b25 title="Редактиране на група" onClick="return group_update(level_group); return false;"><img src=images/edit.gif /></button>&nbsp;
							<button style="width: 30px" name="Button" id=b25 title="Премахване на група" onClick="return group_delete(level_group); return false;"><img src=images/erase.gif /></button>
						{else}
							&nbsp;
						{/if}
					</td>
				</tr>
			</table>
		</center>
		<hr>
	</div>

	<div id="result"></div>

</form>

<script>
	loadXMLDoc('result');
</script>