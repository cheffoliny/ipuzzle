{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( 'load' );
		}
		
		function formSubmit()
		{
			rpc_on_exit = function()
			{
				if( window.opener.loadXMLDoc2 )
				{
					window.opener.loadXMLDoc2( "refreshFilters" );
					window.close();
					
					rpc_on_exit = function() {}
				}
			}
			
			loadXMLDoc2( 'save' );
		}
	</script>
{/literal}

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	
	<div class="page_caption">{if !$nID}Създаване{else}Редактиране{/if} на филтър</div>
	
	<table style="width: 480px; margin-top: 5px; margin-left: 5px;" class="input">
		<tr class="even">
			<td align="left" style="width: 50px;">
				Име:
			</td>
			<td align="left">
				<input id="sFilterName" name="sFilterName" type="text" style="width: 180px;">
			</td>
		</tr>
		<tr>
			<td colspan="2" style="padding-top:15px;">
				<fieldset>
				<legend>Видими полета:</legend>
				<table class="input">
					<tr class="even">
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nShiftsCount" id="nShiftsCount">
						</td>
						<td>
							Брой Смени
						</td>
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nDayShifts" id="nDayShifts">
						</td>
						<td>
							Дневни
						</td>
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nNightShifts" id="nNightShifts">
						</td>
						<td>
							Нощни
						</td>
					</tr>
					<tr class="odd">
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nSickDays" id="nSickDays">
						</td>
						<td>
							Болнични
						</td>
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nLeaveDays" id="nLeaveDays">
						</td>
						<td>
							Отпуск
						</td>
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nOverallShifts" id="nOverallShifts">
						</td>
						<td>
							Общо
						</td>
					</tr>
					<tr class="even">
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nHolidayHours" id="nHolidayHours">
						</td>
						<td>
							Празнични Часове
						</td>
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nExtraHours" id="nExtraHours">
						</td>
						<td>
							Извънредни Часове
						</td>
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nNormHours" id="nNormHours">
						</td>
						<td>
							Брой по Норма
						</td>
					</tr>
					<tr class="even">
						<td style="width: 20px;">
							<input type="checkbox" class="clear" name="nYearExtraHours" id="nYearExtraHours">
						</td>
						<td>
							Изв. Часове за Годината
						</td>
						
						<td colspan="4">&nbsp;</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr class="even">
			<td align="left">
				<input id="nIsDefault" name="nIsDefault" type="checkbox" class="clear">
			</td>
			<td align="left">
				По Подразбиране
			</td>
		</tr>
		<tr>
			<td style="text-align: right;" colspan="2">
				<br>
				<button type="button" class="search" onClick="formSubmit();"> Запиши </button>
				<button type="button" onClick="parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>
	
</form>

<script>
	onInit();
</script>