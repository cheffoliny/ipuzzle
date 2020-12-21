{literal}
	<script>
		rpc_debug = true;
		
		function update()
		{
			select_all_options( 'nomenclatures_current' );
			select_all_options( 'account_opperate_current' );
			select_all_options( 'account_watch_current' );
			
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
	<form action="" method="POST" name="form1" id="form1" onsubmit="update(); return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Добавяне на{/if} касиер</div>
		<br />
		
		<table class="input">
			<tr class="odd">
				<td>
					<fieldset>
					<legend>Служител:</legend>
						<table class="input">
							<tr class="odd">
								<td align="left" width="80px;">Фирма:&nbsp;&nbsp;</td>
								<td>
									<select name="nIDFirm" id="nIDFirm" style="width: 220px;" onchange="loadXMLDoc2( 'loadOffices' );" />
								</td>
							</tr>
							
							<tr class="even">
								<td align="left" width="80px;">Регион:&nbsp;&nbsp;</td>
								<td>
									<select name="nIDOffice" id="nIDOffice" style="width: 220px;" onchange="loadXMLDoc2( 'loadPersons' );" />
								</td>
							</tr>
							
							<tr class="odd">
								<td align="left" width="80px;">Служител:&nbsp;&nbsp;</td>
								<td colspan="3">
									<select name="nIDPerson" id="nIDPerson" style="width: 220px;" />
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<fieldset>
					<legend>Право за разходи по номенклатури:</legend>
						<table>
							<tr>
								<td>
									<select name="nomenclatures_all" id="nomenclatures_all" style="width:300px" size="10" ondblclick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' );" multiple="multiple">
									</select>
								</td>
								<td>
									<button id=b25 name="button" title="Добави Номенклатура" style="width: 20px;" onClick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' ); return false;"><img src=images/mright.gif /></button></br>
									<button id=b25 name="button" title="Премахни Номенклатура" style="width: 20px;" onClick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'left' ); return false;"><img src=images/mleft.gif /></button>
								</td>
								<td>
									<select name="nomenclatures_current[]" id="nomenclatures_current" style="width:300px" size="10" ondblclick="copy_option_to( 'nomenclatures_all', 'nomenclatures_current', 'left' );" multiple="multiple">
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<fieldset>
					<legend>Банкови сметки:</legend>
						<table class="input">
							<tr>
								<td>
									Оперира със сметки:<br />
									<select name="account_opperate_all" id="account_opperate_all" style="width:300px; height: 70px;" size="10" ondblclick="copy_option_to( 'account_opperate_all', 'account_opperate_current', 'right' );" multiple="multiple">
									</select>
								</td>
								<td width="35px">
									&nbsp;
								</td>
								<td>
									Следи движението на сметки:<br />
									<select name="account_watch_all" id="account_watch_all" style="width:300px; height: 70px;" size="10" ondblclick="copy_option_to( 'account_watch_all', 'account_watch_current', 'right' );" multiple="multiple">
									</select>
								</td>
							</tr>
							<tr>
								<td align="center">
									<button id=b25 name="button" title="Добави" style="width: 20px;" onClick="copy_option_to( 'account_opperate_all', 'account_opperate_current', 'right' ); return false;"><img src=images/adown.gif /></button>
									<button id=b25 name="button" title="Премахни" style="width: 20px;" onClick="copy_option_to( 'account_opperate_all', 'account_opperate_current', 'left' ); return false;"><img src=images/aup.gif /></button>
								</td>
								<td width="35px">
									&nbsp;
								</td>
								<td align="center">
									<button id=b25 name="button" title="Добави" style="width: 20px;" onClick="copy_option_to( 'account_watch_all', 'account_watch_current', 'right' ); return false;"><img src=images/adown.gif /></button>
									<button id=b25 name="button" title="Премахни" style="width: 20px;" onClick="copy_option_to( 'account_watch_all', 'account_watch_current', 'left' ); return false;"><img src=images/aup.gif /></button>
								</td>
							</tr>
							<tr>
								<td>
									<select name="account_opperate_current[]" id="account_opperate_current" style="width:300px; height: 70px;" size="10" ondblclick="copy_option_to( 'account_opperate_all', 'account_opperate_current', 'left' );" multiple="multiple">
									</select>
								</td>
								<td width="35px">
									&nbsp;
								</td>
								<td>
									<select name="account_watch_current[]" id="account_watch_current" style="width:300px; height: 70px;" size="10" ondblclick="copy_option_to( 'account_watch_all', 'account_watch_current', 'left' );" multiple="multiple">
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
				<td style="text-align: right;">
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