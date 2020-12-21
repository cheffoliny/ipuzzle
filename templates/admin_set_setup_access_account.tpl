{literal}
<script>
	rpc_debug=true;
	
	InitSuggestForm = function() {	
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'person' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
			}
		}
	}
		
	function onSuggestPerson( aParams ) {
		$('id_person').value = aParams.KEY;
	}
	
	function submit_form() {
		select_all_options('account_regions');
		if( $('selall').value != 0 )
		{
			if( opener.document.getElementById( 'search_rights' ).options.length )
			{
				for( i = 0; i < opener.document.getElementById( 'search_rights' ).options.length; i++ )
					opener.document.getElementById( 'search_rights' ).options[i].selected = true;
			}
		}
		loadXMLDoc('save', 3);
	}
</script>
{/literal}

<form id="form1" name="form1" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="{$id|default:0}" />
	<input type="hidden" name="selall" id="selall" value="{$selall|default:0}" />
	<input type="hidden" name="id_person" id="id_person" value="0" />
	<div class="page_caption">{if $id>0}Редактиране на потребител{else}Добавяне на потребител{/if}</div>

	<div id="search">
		<table  class="input">
			<tr>
				<td align="right">Потр. име:&nbsp;</td>
				<td><input type="text" name="username" id="username" style="width: 200px;" /></td>
			</tr>
			
			<tr>
				<td align="right">Фирма:&nbsp;</td>
				<td>
					<select name="nIDFirm" id="nIDFirm" style="width: 350px;" onChange="loadXMLDoc('result');"></select>
				</td>
			</tr>

			<tr>
				<td align="right">Служител:&nbsp;</td>
				<td>
					<input name="person" id="person" type="text" suggest="suggest" queryType="person" queryParams="nIDFirm" style="width: 350px;" />&nbsp;&nbsp;
				</td>
			</tr>
			
			<tr>
				<td align="right">Профил:&nbsp;</td>
				<td>
					<select name="id_profile" id="id_profile" style="width: 350px;" >
					</select>
				</td>
			</tr>
			
			<tr>
				<td align="right">Лимит:&nbsp;</td>
				<td><input type="text" name="row_limit" id="row_limit" style="width: 200px;" value="20" /> реда в резултат от справка</td>
			</tr>
			
			<tr>
				<td colspan="4">
					<fieldset>
					<legend>Достъп до фирма/регион</legend>
						<table>
							<tr>
								<td>
									<select name="all_regions" id="all_regions" size="10" ondblclick="move_option_to( 'all_regions', 'account_regions', 'right');" multiple>
									</select>
								</td>
								<td>
									<button class="search" style="width: 50px;" name="button" title="Добави регион" onClick="move_option_to( 'all_regions', 'account_regions', 'right'); return false;"><img src="images/mright.gif" /></button></br>
									<button name="button" style="width: 50px;" title="Премахни регион" onClick="move_option_to( 'all_regions', 'account_regions', 'left'); return false;"><img src="images/mleft.gif" /></button>
								</td>
								<td>
									<select name="account_regions[]" id="account_regions" size="10" ondblclick="move_option_to( 'all_regions', 'account_regions', 'left');" multiple>
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
		<table width="100%" cellspacing="5px">
			<tr><td align="right" valign="bottom">
				<button type="button" onClick="submit_form();" class="search"> Запиши </button>
				<button onClick="parent.window.close();"> Затвори </button>
			</td></tr>
		</table>
	</div>
</form>

<script>
	loadXMLDoc('result');
</script>
