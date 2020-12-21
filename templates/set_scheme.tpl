{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( 'load' );
		}
		
		function update()
		{
			select_all_options( 'nomenclatures_current' );
			loadXMLDoc2( 'save', 3 );
			return false;
		}
		
		function select_none( id )
		{
			oSEL = document.getElementById( id );
			if( oSEL.options.length )
			{
				for( i = 0; i < oSEL.options.length; i++ )oSEL.options[i].selected = false;
				return true;
			}
			else return false;
		}
		
		function changeNomenclatures()
		{
			$('fromList').value = $('nomenclatures_all').value;
			$('toList').value = $('nomenclatures_current').value;
			
			select_all_options( 'nomenclatures_current' );
			
			rpc_on_exit = function( nCode )
			{
				if( !parseInt( nCode ) )
				{
					select_none( 'nomenclatures_current' );
					
					$('nomenclatures_all').value = $('fromList').value;
					$('nomenclatures_current').value = $('toList').value;
				}
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( 'refreshDetectors' );
		}
		
		function copy_option_to( lid, rid, direction )
		{
			clear_flag = false;
			if( direction == 'right' )
			{
				sid = lid;
				id = rid;
			}
			else
			{
				sid = rid;
				id = lid;
			}
			obj = document.getElementById( sid );
			
			if( obj.options.length && obj.selectedIndex != -1 )
			{
				while( obj.selectedIndex != -1 )
				{
					OPT = obj.options[obj.selectedIndex];
					vSEL = document.getElementById(id);
					nOPT = document.createElement( 'OPTION' );
					nOPT.value = OPT.value;
					nOPT.text = OPT.text;
					
					for( i = 0; i < vSEL.options.length; i++ )
					{
						if( vSEL.options[i].text > nOPT.text && vSEL.options[i].value != '' )break;
					}
					
					if( OPT.value == '' )
					{
						i = 0;
						clear_flag = true;
					}
					vSEL.add( nOPT, ( isIE ) ? i : vSEL.options[i] );
					obj.options[obj.selectedIndex].selected = false;
					//obj.remove(obj.selectedIndex);
				}
				
				if( direction == 'right' && clear_flag )
				{
					while( vSEL.options.length > 1 )
						vSEL.remove( 1 );
					
					obj.disabled = true;
				}
				
				if( direction == 'left' && clear_flag )
				{
					vSEL.disabled = false;
				}
			}
		}
		
		function remove_selected( lid, rid, direction )
		{
			clear_flag = false;
			if( direction == 'right' )
			{
				sid = lid;
				id = rid;
			}
			else
			{
				sid = rid;
				id = lid;
			}
			
			obj = document.getElementById( sid );
			if( obj.options.length && obj.selectedIndex != -1 )
			{
				while( obj.selectedIndex != -1 )
				{
					OPT = obj.options[obj.selectedIndex];
					vSEL = document.getElementById(id);
					nOPT = document.createElement( 'OPTION' );
					nOPT.value = OPT.value;
					nOPT.text = OPT.text;
					
					for( i = 0; i < vSEL.options.length; i++ )
					{
						if( vSEL.options[i].text > nOPT.text && vSEL.options[i].value != '' )break;
					}
					
					if( OPT.value == '' )
					{
						i = 0;
						clear_flag = true;
					}
					//vSEL.add(nOPT, (isIE) ? i : vSEL.options[i]);
					//obj.options[obj.selectedIndex].selected = false;
					obj.remove(obj.selectedIndex);
				}
				
				if( direction == 'right' && clear_flag )
				{
					while( vSEL.options.length > 1 )
						vSEL.remove( 1 );
					
					obj.disabled = true;
				}
				
				if( direction == 'left' && clear_flag )
				{
					vSEL.disabled = false;
				}
			}
		}
		
		function changeDefault()
		{
			var nDefault = document.getElementById( "nDefault" ).checked;
			
			if( nDefault )
			{
				document.getElementById( "nIDDetector" ).disabled = "";
			}
			else
			{
				document.getElementById( "nIDDetector" ).disabled = "disabled";
				document.getElementById( "nIDDetector" ).value = "0";
			}
		}
	</script>
{/literal}

<div class="page_caption">{if $nID>0}Редактиране на Шаблон{else}Нов шаблон{/if}</div>

<form id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID|default:0}">
	<input type="hidden" name="id" id="id" value="{$id|default:0}">
	
	<input type="hidden" name="fromList" id="fromList" value="">
	<input type="hidden" name="toList" id="toList" value="">
	
	<div id="builder">
		<table class="input">
			<tr>
				<td colspan=2>
					Наименование:&nbsp;
					<input type="text" name="name" id="name" size="53" />
				</td>
			</tr>
			<tr>
				<td colspan=2 id=fieldset>
					<fieldset style="border: 1px solid black">
					<legend>Номенклатури</legend>
						<table>
							<tr>
								<td>
									<select name="nomenclatures_all" id="nomenclatures_all" style="width:300px" size="10" ondblclick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' ); changeNomenclatures();" multiple="multiple">
									</select>
								</td>
								<td>
									<button id=b25 name="button" title="Добави Номенклатура" onClick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' ); changeNomenclatures(); return false;"><img src=images/mright.gif /></button></br>
									<button id=b25 name="button" title="Премахни Номенклатура" onClick="remove_selected( 'nomenclatures_all', 'nomenclatures_current', 'left' ); changeNomenclatures(); return false;"><img src=images/mleft.gif /></button>
								</td>
								<td>
									<select name="nomenclatures_current[]" id="nomenclatures_current" style="width:300px" size="10" ondblclick="remove_selected( 'nomenclatures_all', 'nomenclatures_current', 'left' ); changeNomenclatures();" multiple="multiple">
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td colspan=2>&nbsp;</td>
			</tr>
			<tr>
				<td align="left">
					<input type="checkbox" id="nDefault" name="nDefault" class="clear" onclick="changeDefault();">&nbsp;Шаблон за ел. договори
				</td>
				<td align="right">
					Номенклатура Детектор:&nbsp;
					<select id="nIDDetector" name="nIDDetector" class="select250" />
				</td>
			</tr>
			<tr>
				<td colspan=2>&nbsp;</td>
			</tr>
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
	 onInit();
</script>