{literal}
	<script>
		rpc_debug=true;
		
		var my_action = '';
		
		function update()
		{
			select_all_options( 'directions_current' );
			
			my_action = 'save';
			return loadXMLDoc( 'save', 3 );
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
	<form action="" method="POST" name="form1" id="form1" onsubmit="return update();">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="id_f" name="id_f" value="{$id_f}">
		
		<div class="page_caption">{if $id}Редактиране на Регион{else}Нов Регион{/if}</div>
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="400">Име на Фирмата:</td>
				<td>
					<select name="id_firm" id="id_firm" style="width: 270px;" />
				</td>
			</tr>
			<tr class="even">
				<td width="400">Код / Име на Региона:</td>
				<td>
					<input type="text" class="inp50" name="code" id="code" onkeypress="return formatDigits(event);" />&nbsp;/&nbsp;
					<input type="text" name="name" id="name" class="inp200" />
				</td>
			</tr>
			<tr class="odd">
				<td>&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr class="even">
				<td width="400">Коефициент наработка за монтаж/профилактика:</td>
				<td><input type="text" name="factor_tech_support" id="factor_tech_support" class="inp50" onkeypress="return formatMoney(event);" /></td>
			</tr>
			<tr class="odd">
				<td width="400">Коефициент на отдалеченост на километър за техник:</td>
				<td><input type="text" name="factor_tech_distance" id="factor_tech_distance" class="inp50" onkeypress="return formatMoney(event);" /></td>
			</tr>
			<tr class="even">
				<td width="400">Коефициент цена преразход на километър:</td>
				<td><input type="text" name="factor_km_over" id="factor_km_over" class="inp50" onkeypress="return formatMoney(event);" /></td>
			</tr>
			<tr class="odd">
				<td width="400">Коефициент цена икономия на километър:</td>
				<td><input type="text" name="factor_km_below" id="factor_km_below" class="inp50" onkeypress="return formatMoney(event);" /></td>
			</tr>
			<tr class="even">
				<td width="400">Коефициент аранжиране еднократно задължение:</td>
				<td><input type="text" name="factor_object_single_from_arrange" id="factor_object_single_from_arrange" class="inp50" onkeypress="return formatMoney(event);" /></td>
			</tr>
			<tr class="odd">
				<td width="400">Признати километри за пътен лист:</td>
				<td><input type="text" name="km_per_roadlist" id="km_per_roadlist" class="inp50" onkeypress="return formatMoney(event);" /></td>
			</tr>
			<tr class="even">
				<td width="400">Признати брой обходи за пътен лист:</td>
				<td><input type="text" name="max_visits" id="max_visits" class="inp50" onkeypress="return formatDigits(event);" /></td>
			</tr>
			<tr class="odd">
				<td>&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr class="even">
				<td width="400">Регион-администрация:</td>
				<td><input type="checkbox" name="nIsAdmin" id="nIsAdmin" class="clear" /></td>
			</tr>
			<tr class="odd">
				<td width="400">Регион Техническа Поддръжка:</td>
				<td><input type="checkbox" name="nIsTech" id="nIsTech" class="clear" /></td>
			</tr>
			<tr class="even">
				<td width="400">Регион-реакция:</td>
				<td><input type="checkbox" name="nIsReaction" id="nIsReaction" class="clear" /></td>
			</tr>
		</table>
		
		<br />
		
		<fieldset>
		<legend>Адрес:</legend>
			<table class="input">
				<tr class="odd">
					<td>Град:</td>
					<td><select name="nAddressCity" id="nAddressCity" class="select200" onchange="loadXMLDoc( 'updateareas' );" /></td>
					
					<td style="width: 40px;">&nbsp;</td>
					
					<td>Местност:</td>
					<td><select name="nAddressArea" id="nAddressArea" class="select200" /></td>
				</tr>
				<tr class="even">
					<td>Улица:</td>
					<td><select name="nAddressStreet" id="nAddressStreet" class="select200" /></td>
					
					<td style="width: 40px;">&nbsp;</td>
					
					<td>Номер:</td>
					<td><input type="text" name="nNumber" id="nNumber" class="inp50" onkeypress="return formatDigits(event);" /></td>
				</tr>
			</table>
		</fieldset>
		
		<br />
		
		<fieldset>
		<legend>Направления:</legend>
			<table>
				<tr>
					<td>
						<select name="directions_all" id="directions_all" style="width:293px" size="4" ondblclick="copy_option_to( 'directions_all', 'directions_current', 'right' );" multiple="multiple">
						</select>
					</td>
					<td>
						<button id=b25 name="button" title="Добави Направление" style="width: 20px;" onClick="copy_option_to( 'directions_all', 'directions_current', 'right' ); return false;"><img src=images/mright.gif /></button></br>
						<button id=b25 name="button" title="Премахни Направление" style="width: 20px;" onClick="copy_option_to( 'directions_all', 'directions_current', 'left' ); return false;"><img src=images/mleft.gif /></button>
					</td>
					<td>
						<select name="directions_current[]" id="directions_current" style="width:293px" size="4" ondblclick="copy_option_to( 'directions_all', 'directions_current', 'left' );" multiple="multiple">
						</select>
					</td>
				</tr>
			</table>
		</fieldset>
		
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
		loadXMLDoc( 'result' );
		
		rpc_on_exit = function( err )
		{
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc( 'result' );
				
				my_action = '';
			}
		}
	</script>
{/literal}