{literal}
	<script>
		rpc_debug = true;
		
		function GenerateBothRegionsAndPersons()
		{
			rpc_on_exit = function ( nCode )
			{
				if( !parseInt( nCode ) )
				{
					rpc_on_exit = function () {}
					loadXMLDoc2( 'genpersons' );
				}
			}
			
			loadXMLDoc2( 'genregions' );
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="select_all_options('selected_offices'); loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="sOfficesParam" name="sOfficesParam" value="{$sOfficesParam}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} РС</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td align="right">Фирма:</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="GenerateBothRegionsAndPersons();" />&nbsp;&nbsp;
				</td>
			</tr>
			<tr class="even">
				<td align="right">Регион:</td>
				<td>
					<select class="default" name="nIDRegion" id="nIDRegion" onchange="loadXMLDoc2( 'genpersons' );" />
				</td>
			</tr>
			<tr class="odd">
				<td align="right">Рекламен сътрудник:</td>
				<td>
					<select class="default" name="nIDPerson" id="nIDPerson" />
				</td>
			</tr>
			<tr class="odd">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr class="even">
				<td width="200">Пореден номер за ел. Договор:</td>
				<td>
					<input type="text" name="nNextNum" id="nNextNum" class="inp100" onkeypress="return formatDigits(event);" />
				</td>
			</tr>
			<tr class="even">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr class="odd">
				<td colspan=2 id=fieldset>
					<fieldset style="border: 1px solid black">
					<legend>Населени места, в които работи сътрудника:</legend>
						<table>
							<tr>
								<td>
									<select name="Offices" id="Offices" style="width:200px" size="10" ondblclick="move_option_to( 'Offices', 'selected_offices', 'right');" multiple="multiple">
									</select>
								</td>
								<td>
									<button id=b25 name="button" title="Добави Файл" onClick="move_option_to( 'Offices', 'selected_offices', 'right' ); return false;"><img src=images/mright.gif /></button></br>
									<button id=b25 name="button" title="Премахни файл" onClick="move_option_to( 'Offices', 'selected_offices', 'left' ); return false;"><img src=images/mleft.gif /></button>
								</td>
								<td>
									<select name="selected_offices[]" id="selected_offices"  style="width:200px" size="10" ondblclick="move_option_to( 'Offices', 'selected_offices', 'left');" multiple="multiple">
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
				<td>&nbsp;</td>
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

{literal}
	<script>
		var myID = $('nID').value;
		
		if( myID != 0 )
		{
			rpc_on_exit = function ( nCode )
			{
				if( !parseInt( nCode ) )
				{
					//Extract Array
					var sOfficesParam = $('sOfficesParam').value;
					var oOffices = document.getElementById( 'Offices' );
					
					var a = sOfficesParam.split(' ');
					
					for( var q = 0; q < oOffices.options.length; q++ )
					{
						for( var i = 0; i < a.length; i++ )
						{
							if( a[i] != '' && a[i] != ' ' )
							{
								//alert( a[i] + ' ' + oOffices.options[q].value );
								if( a[i] == oOffices.options[q].value )
								{
									oOffices.options[q].selected = true;
								}
							}
						}
					}
					move_option_to( 'Offices', 'selected_offices', 'right' );
					//End Extract Array
				}
				rpc_on_exit = function () {}
			}
		}
		
		loadXMLDoc2('get');
	</script>
{/literal}