{literal}
	<script>
		rpc_method='POST';
		rpc_debug=true;
	
		function update()
		{
			if( $('selall').value != 0 )
			{
				if( opener.document.getElementById( 'search_rights' ).options.length )
				{
					for( i = 0; i < opener.document.getElementById( 'search_rights' ).options.length; i++ )
						opener.document.getElementById( 'search_rights' ).options[i].selected = true;
				}
			}
			loadXMLDoc('update', 3);
		}
	
		function check_all()
		{
			if( obj=document.getElementById('all_levels') )
			{
				flag = obj.checked;
				oSEL = document.getElementsByTagName("INPUT");
				for( i = 0; i < oSEL.length; i++ )
					if( oSEL[i].type == 'checkbox' && oSEL[i].name.substr(0,5)==('level') )
					{
						if( flag ) oSEL[i].checked = false;
						oSEL[i].disabled = flag;
					}
			}
		}
	</script>
{/literal}

<div class="page_caption">{if $id}Редактиране на профил{else}Нов профил{/if}</div>

<form action="" id="form1" onSubmit="update(); return false">
	<input type="hidden" name="id" id="id" value="{$id|default:0}">
	<input type="hidden" name="selall" id="selall" value="{$selall|default:0}" />
	<input type="hidden" name="save_like_new" id="save_like_new" value="no">
	
	<div class="content" id="builder">
		<table class="input">
			<tr>
				<td align="right">Име</td>
				<td><input type="text" name="name" id="name" size="53" /></td>
			</tr>
			<tr>
				<td align="right">Описание</td>
				<td><input type="text" name="description" id="description" size="125" /></td>
			</tr>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="checkbox" name="all_levels" id="all_levels" class="clear" value="1" onClick=" check_all()">Всички нива&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="is_default" id="is_default" class="clear" value="1">Профил по подразбиране
				</td>
			<tr>
				<td colspan=2>
					<div style="height:400px; width:100%; overflow:auto">
						<table class="input">
							{foreach from=$level_groups item=group}
								<tr bgcolor="#DBE7FF">
									<td colspan=4 align="left"><strong>{$group.name|escape:"html"}</strong></td>
								</tr>
								{foreach from=$levels item=level}
									{if $level.id_group eq $group.id}
										<tr bgcolor="{cycle values="white,#F2F3F7,"}">
											<td>&nbsp;</td>
											<td><input type="checkbox" name="level_{$level.id}" id="level_{$level.id}" class="clear" value="1"></td>
											<td nowrap>{$level.name|escape:"html"}</td>
											<td>{$level.description|escape:"html"}</td>
										</tr>
									{/if}
								{/foreach}
							{/foreach}
						</table>
					</div>
				</td>
			</tr>
		</table>

        <table width="100%">
            <tr>
                {if $id}
                    <td>
                        <button class="btn btn-xs btn-primary" name="Button"  onclick="$('save_like_new').value='yes';update();"><i class="fa fa-check"></i> Запиши като нов</button>
                    </td>
                {/if}
                <td align="right" valign="bottom">
                    <button type=submit class="btn btn-xs btn-success" id="b100" onclick="update();"><i class="fa fa-plus"></i> Добави </button>
                    <button type=submit class="btn btn-xs btn-success" id="b100" onClick="parent.window.close()"><img src="images/cancel.gif" />Затвори</button>
                </td>
            </tr>
        </table>

    </div>


</form>

{literal}
	<script>
		rpc_on_exit = function() { check_all(); }
		loadXMLDoc('result');
	</script>
{/literal}