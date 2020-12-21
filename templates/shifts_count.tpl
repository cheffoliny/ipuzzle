{literal}
	<script>
		rpc_debug = true;
		
		function showFilters()
		{
			if(	$('show_filters').value == "show" )
			{
				$('filters').style.display = "none";
				$('show_filters').value = "hide";
				
			}
			else
			{
				$('filters').style.display = "block";
				$('show_filters').value = "show";
			}
			
			if( typeof( xslResizer ) == 'function' )
			{
				xslResizer();
			}
		}
		
		function editPersonnel( id )
		{
			dialogPerson( id );
		}
		
		function onClickRadioMonth()
		{
			$('nRadio').value = '1';
		}
		
		function onClickRadioPeriod()
		{
			$('nRadio').value = '2';
		}
		
		function openFilter( type )
		{
			var id;
			if( type == 1 )
			{
				dialogShiftsCountFilter(0);
			}
			else
			{
				id = $('nIDScheme').value;
				if( id != 0 )
				{
					dialogShiftsCountFilter( id );
				}
			}
		}
		
		function deleteFilter()
		{
			if( $("nIDScheme").value != 0 )
			
			if( confirm( 'Наистина ли желаете да премахнeте филтърът?' ) )
			{
				rpc_on_exit = function()
				{
					rpc_on_exit = function() {}
					
					loadXMLDoc2( "refreshFilters", 1 );
				}
				
				loadXMLDoc2( "deleteFilter" );
			}
		}
	</script>
{/literal}

<dlcalendar click_element_id="img_date_from" input_element_id="date_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_to" input_element_id="date_to" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="show_filters" id="show_filters" value="show" />
	<input type="hidden" name="nID" id="nID" value="0" />
	<input type="hidden" name="nRadio" id="nRadio" value="1" />
	
	<table class="page_data">
		<tr>
			<td class="page_name">Брой Смени</td>
			<td align="right">
				<button type="button" style="width: 30px;" onClick="showFilters();"><img src="images/search2.gif"></button>
			</td>
		</tr>
	</table>
	
	<div id="filters">
		<table class="search">
			<tr>
				<td style="width: 30px">&nbsp;</td>
				<td align="right">Филтри</td>
				<td align="left">
					<select name="nIDScheme" id="nIDScheme"></select>
				</td>
				<td align="left" colspan="8">
					<button style="width: 30px" id=b25 title="Нов филтър" name="Button5" onClick="openFilter( 1 );" ><img src="images/plus.gif" /></button>&nbsp;
					<button style="width: 30px" name="Button4" id=b25 title="Редактиране на филтър" onClick="openFilter( 2 );"><img src=images/edit.gif /></button>&nbsp;
					<button style="width: 30px" name="Button3" id=b25 title="Премахване на филтър" onClick="deleteFilter();"><img src=images/erase.gif /></button>
				</td>
			</tr>
			<tr>
				<td colspan="11">&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 30px">&nbsp;</td>
				<td align="right">Фирма</td>
				<td align="left">
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'genregions' );" />
				</td>
			
				
				<td align="right">Регион</td>
				<td align="left">
					<select class="default" name="nIDRegion" id="nIDRegion" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				<td align="right">Вид смяна:&nbsp;</td>
				<td align="left">
					<select class="select100" name="sShiftType" id="sShiftType">
						<option value="all">-- Всички --</option>
						<option value="day">Дневна</option>
						<option value="night">Нощна</option>
						<option value="leave">Отпуск</option>
						<option value="full">24-часова</option>
					</select>
				</td>			
				<td style="width: 20px;">&nbsp;</td>
				<td align="right">Името започва с</td>
				<td align="left">
					<input type="text" name="sNameStart" id="sNameStart" class="inp100">
				</td>
			</tr>
			<tr>
				<td style="width: 30px">&nbsp;</td>
				<td colspan="2" align="left">
					<input type="radio" class="clear" id="type" name="types" value="rMonth" checked="checked" onClick = "onClickRadioMonth();" />
					по месец
				</td>
				
				<td align="right">Месец</td>
				<td align="left">
					<select class="select150" name="sYearMonth" id="sYearMonth" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 30px">&nbsp;</td>
				<td colspan="2" align="left">
					<input type="radio" class="clear" id="type" name="types" value="rPeriod" onClick = "onClickRadioPeriod();" />
					по период
				</td>	
				
				<td colspan="2" align="right">
				&nbsp;&nbsp;от дата&nbsp;<input type="text" id="date_from" name="date_from" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_from" />&nbsp;
				до дата&nbsp;<input type="text" id="date_to" name="date_to" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
							<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_to" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				<td align="right">Длъжност:&nbsp;</td>
				<td align="left">
					<select class="default" name="nIDPosition" id="nIDPosition" />
				</td>
				
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				
				<td align="right">
					<button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif">Търси</button>
				</td>
			</tr>
		</table>
	</div>
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>