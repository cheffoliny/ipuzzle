{literal}
	<script>
		rpc_debug = true;
		rpc_method = "POST";
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		
		function getResult() {
			select_all_options('account_earnings');
			select_all_options('account_expenses');
			loadXMLDoc2('save',5)
		}

	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<table class = "page_data">
		<tr>
			<td class="page_name">{if !$nID }Добавяне{else}Редактиране{/if} на шаблон с филтри</td>
			<td align="right">
				Име:
			</td>
			<td>
				<input style="width:200px;" type="text" id="name" name="name"/>
			</td>
		</tr>
	</table>

	<table border="0" height="350px;">
		<tr>
			<td>
				<table>
					<tr>
						
						<td style="width:250px;"  valign="top" align="center">
								<fieldset style="width: 200px; height:300px;">
								<legend>Трудов договор</legend>
								<table>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="fix_salary" id="fix_salary"/>  фиксирана заплата 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="min_salary" id="min_salary" />  минимална заплата 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="insurance" id="insurance"/>  осигорителна ставка 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="trial" id="trial"/>  пробен период
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="due_days" id="due_days"/>  полагаем отпуск 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="used_days" id="used_days"/>  използван отпуск 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="remain" id="remain"/>  оставащ отпуск 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear" name="egn" id="egn"/>  ЕГН 
										</td>
									</tr>									
								</table>
								</fieldset>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<table>
					<tr>
						
						<td style="width:250px;" valign="top" align="center">
							<fieldset style="width: 200px;height:300px;">
							<legend>Наработки</legend>
								<table>
									<tr style="height: 5px;"><td></td></tr>
									<tr class="even">
										<td>
											<select name="all_earnings" id="all_earnings" size="10"  style="width: 100px; height:250px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'right');" multiple>
											</select>
										</td>
										<td>
											<button class="search" style="width: 50px;" name="button" title="Добави наработка" onClick="move_option_to( 'all_earnings', 'account_earnings', 'right'); return false;"><img src="images/mright.gif" /></button></br>
											<button name="button" style="width: 50px;" title="Премахни наработка" onClick="move_option_to( 'all_earnings', 'account_earnings', 'left'); return false;"><img src="images/mleft.gif" /></button>
										</td>
										<td>
											<select name="account_earnings[]" id="account_earnings" size="10" style="width: 100px;height:250px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'left');" multiple>
											</select>
										</td>
									</tr>
									<tr style="height: 5px;"><td></td></tr>
								</table>
							</fieldset>
						</td>
						
					</tr>
				</table>
			
			</td>
			<td>
				<table>
					<tr>
						<td style="width:200px;" valign="top" align="center">
							<fieldset style="width: 150px; height:300px;">
							<legend>Удръжки</legend>
								<table>
									<tr style="height: 5px;"><td></td></tr>
									<tr class="even">
										<td>
											<select name="all_expenses" id="all_expenses" size="10"  style="width: 100px;height:250px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'right');" multiple>
											</select>
										</td>
										<td>
											<button class="search" style="width: 50px;" name="button" title="Добави удръжка" onClick="move_option_to( 'all_expenses', 'account_expenses', 'right'); return false;"><img src="images/mright.gif" /></button></br>
											<button name="button" style="width: 50px;" title="Премахни удръжка" onClick="move_option_to( 'all_expenses', 'account_expenses', 'left'); return false;"><img src="images/mleft.gif" /></button>
										</td>
										<td>
											<select name="account_expenses[]" id="account_expenses" size="10" style="width: 100px;height:250px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'left');" multiple>
											</select>
										</td>
									</tr>
									<tr style="height: 5px;"><td></td></tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</table>
			
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;
			</td>
			<td>
				<input type="checkbox" class="clear" name="ear_exp" id="ear_exp"/>  наработки - удръжки 
			</td>
		</tr>
		<tr class="odd">
			<td colspan="2" width="250">&nbsp;</td>
			<td style="text-align:right;">
				<button type="button" class="search" onClick="getResult();"> Запиши </button>
				<button type="button" onClick="parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>


</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}