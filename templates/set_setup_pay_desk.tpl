{literal}
	<script>
		
		rpc_debug = true;
		
		function update()
		{
			select_all_options( 'persons_current' );
			
			loadXMLDoc2( 'save', 3 );
			return false;
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
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="update();">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Добавяне на{/if} касов апарат</div>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td>
					Номер:&nbsp;
					<input type="text" class="inp150" name="sNum" id="sNum" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr class="odd">
				<td>
					<fieldset>
					<legend>Зачислен на:</legend>
						<table class="input">
							<tr class="odd">
								<td>
									<select name="persons_all" id="persons_all" style="width:270px" size="10" ondblclick="copy_option_to( 'persons_all', 'persons_current', 'right' );" multiple="multiple">
									</select>
								</td>
								<td>
									<button id=b25 name="button" title="Добави Служител" style="width: 20px;" onClick="copy_option_to( 'persons_all', 'persons_current', 'right' ); return false;"><img src=images/mright.gif /></button></br>
									<button id=b25 name="button" title="Премахни Служител" style="width: 20px;" onClick="copy_option_to( 'persons_all', 'persons_current', 'left' ); return false;"><img src=images/mleft.gif /></button>
								</td>
								<td>
									<select name="persons_current[]" id="persons_current" style="width:270px" size="10" ondblclick="copy_option_to( 'persons_all', 'persons_current', 'left' );" multiple="multiple">
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2( 'get' );
</script>