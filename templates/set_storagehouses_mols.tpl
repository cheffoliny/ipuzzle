{literal}
	<script>
		rpc_debug = true;
		
		function processPerson( field1, field2, direction )
		{
			if( direction == "right" )
			{
				if( $('sPersonList').value != '' )
				{
					var loadedPersons = $('sPersonList').value.split( "," );
					var loadedPersonsIndex = loadedPersons.length;
				}
				else
				{
					var loadedPersons = new Array();
					var loadedPersonsIndex = 0;
				}
				
				//Проверяваме дали някой от избраните служители не е нулев.
				for( i = 0; i < $('all_persons').options.length; i++ )
				{
					if( $('all_persons').options[i].selected == true )
					{
						if( $('all_persons').options[i].value == 0 )
						{
							$('all_persons').options[i].selected = false;
						}
						else
						{
							loadedPersons[loadedPersonsIndex] = $('all_persons').options[i].value;
							loadedPersonsIndex++;
						}
					}
				}
				
				$('sPersonList').value = loadedPersons.join( "," );
			}
			
			if( direction == "left" )
			{
				if( $('sPersonList').value != '' )
				{
					var loadedPersons = $('sPersonList').value.split( "," );
				}
				else
				{
					var loadedPersons = new Array();
				}
				
				//Проверяваме кои елементи да се премахнат.
				for( i = 0; i < $('sel_persons').options.length; i++ )
				{
					if( $('sel_persons').options[i].selected == true )
					{
						for( q = 0; q < loadedPersons.length; q++ )
						{
							if( $('sel_persons').options[i].value == loadedPersons[q] )
							{
								loadedPersons.splice( q, 1 );
							}
						}
					}
				}
				
				$('sPersonList').value = loadedPersons.join( "," );
			}
			
			//Adding
			move_option_to( field1, field2, direction );
			//End Adding
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="sPersonList" name="sPersonList" value="">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} склад</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=set_storagehouses_tabs.tpl}</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					<table class="input">
						<tr class="odd">
							<td align="right">Фирма:</td>
							<td>
								<select name="nIDFirm" id="nIDFirm" style="width: 220px;" onchange="loadXMLDoc2( 'loadOffices' )" />
							</td>
						</tr>
						<tr class="even">
							<td align="right">Регион:</td>
							<td>
								<select name="nIDOffice" id="nIDOffice" style="width: 220px;" onchange="loadXMLDoc2( 'loadPersons' )" />
							</td>
						</tr>
						<tr class="even">
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr class="odd">
							<td colspan="2">
								<table border="0">
									<tr>
										<td align="center">
											<select name="all_persons" id="all_persons" size="6"  style="width: 300px;" ondblclick="processPerson( 'all_persons', 'sel_persons', 'right' );" multiple>
											</select>
										</td>
									</tr>
									<tr>
										<td align="center">
											<button class="search" style="width: 50px;" name="button" title="Добави МОЛ" onClick="processPerson( 'all_persons', 'sel_persons', 'right' ); return false;"><img src="images/adown.gif" /></button>
											&nbsp;
											<button name="button" style="width: 50px;" title="Премахни МОЛ" onClick="processPerson( 'all_persons', 'sel_persons', 'left' ); return false;"><img src="images/aup.gif" /></button>
										</td>
									</tr>
									<tr>
										<td align="center">
											<select name="sel_persons[]" id="sel_persons" size="6" style="width: 300px;" ondblclick="processPerson( 'all_persons', 'sel_persons', 'left' );" multiple>
											</select>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					
					<table class="input">
						<tr class="odd">
							<td width="250">&nbsp;</td>
							<td style="text-align:right;">
								<button type="submit" class="search" onclick="loadXMLDoc2( 'save' );"> Запиши </button>
								<button onClick="parent.window.close();"> Затвори </button>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>

<script>
	loadXMLDoc2( 'load' );
</script>