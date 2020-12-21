{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( "load" );
		}
		
		function formSubmit()
		{
			loadXMLDoc2( "save", 5 );
		}
	</script>
{/literal}

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	
	<div class="page_caption">{if !$nID}Създаване{else}Редактиране{/if} на филтър</div>
	
	<table style="margin-top: 20px;" class="input">
		<tr class="even">
			<td align="right" style="width: 50px;">Име:&nbsp;</td>
			<td>
				<input id="sFilterName" name="sFilterName" type="text" style="width: 180px;">
			</td>
			
			<td align="right">
				<input id="nIsDefault" name="nIsDefault" type="checkbox" class="clear">
			</td>
			<td>
				По Подразбиране
			</td>
		</tr>
		<tr>
			<td colspan="4" style="padding-top: 15px;">
				<fieldset>
					<legend>Видими полета</legend>
					<table class="input">
						<tr class="even">
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="person_name" id="person_name">
							</td>
							<td>Служител</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="date_from" id="date_from">
							</td>
							<td>Дата на Назначаване</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="vacate_date" id="vacate_date">
							</td>
							<td>Дата на Напускане</td>
						</tr>
						
						<tr class="odd">
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="office_name" id="office_name">
							</td>
							<td>Регион</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="object_name" id="object_name">
							</td>
							<td>Обект</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="min_cost" id="min_cost">
							</td>
							<td>Основна по ТД</td>
						</tr>
						
						<tr class="even">
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="unpaid_count" id="unpaid_count">
							</td>
							<td>Непл. Отпуск</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="leave_count" id="leave_count">
							</td>
							<td>Пл. Отпуск</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="vouchers_plus" id="vouchers_plus">
							</td>
							<td>+ ВАУЧЕРИ</td>
						</tr>
						
						<tr class="odd">
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="correction_five" id="correction_five">
							</td>
							<td>Корекция 5</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="vouchers_plus_c" id="vouchers_plus_c">
							</td>
							<td>+ ВАУЧЕРИ ( Текущ )</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="correction_five_c" id="correction_five_c">
							</td>
							<td>Корекция 5 ( Текущ )</td>
						</tr>
						
						<tr class="even">
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="workdays" id="workdays">
							</td>
							<td>Работни Дни</td>
							
							<td style="width: 20px;">
								<input type="checkbox" class="clear" name="vouchers" id="vouchers">
							</td>
							<td>+ Допълнителни</td>
							
							<td colspan="2">&nbsp;</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td style="text-align: right;" colspan="4">
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