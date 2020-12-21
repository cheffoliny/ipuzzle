<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция{else}Добавяне{/if}</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td>
					Тип:&nbsp;
					<select name="sType" id="sType" class="select200">
						<option value='mdo'>Месечна Денонощна Охрана</option>
						<option value='tp'>ПЛАН</option>
						<option value='mon'>Мониторинг на Обект</option>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td>&nbsp;</td>
			</tr>
			<tr class="even">
				<td>
					<fieldset>
					<legend>Месечни Такси:</legend>
						
						<table class="input">
							<tr class="odd">
								<td width="330">Такса за 1 детектор:</td>
								<td>
									<input type="text" name="nBasePrice" id="nBasePrice" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
							<tr class="even">
								<td width="330">Стъпка оскъпяване за допълнителен детектор:</td>
								<td>
									<input type="text" name="nFactorDetector" id="nFactorDetector" class="inp50" onkeypress="return formatMoney(event);" />
								</td>
							</tr>
							<tr class="odd">
								<td width="330">Коефициент поевтиняване за закупена техника:</td>
								<td>
									<input type="text" name="nKClientTech" id="nKClientTech" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; %
								</td>
							</tr>
							<tr class="odd">
								<td width="330">&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr class="even">
								<td width="330">Цена за радио паник бутон:</td>
								<td>
									<input type="text" name="nPriceRadioPanic" id="nPriceRadioPanic" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
							<tr class="odd">
								<td width="330">Цена за стационарен паник бутон:</td>
								<td>
									<input type="text" name="nPriceStaticPanic" id="nPriceStaticPanic" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
							<tr class="even">
								<td width="330">Цена за клавиатурен паник:</td>
								<td>
									<input type="text" name="nPriceKbdPanic" id="nPriceKbdPanic" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
							<tr class="odd">
								<td width="330">Цена за сметка онлайн:</td>
								<td>
									<input type="text" name="nPriceOnlineBill" id="nPriceOnlineBill" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
							<tr class="even">
								<td width="330">Цена за „Вест”:</td>
								<td>
									<input type="text" name="nPriceTelepolVest" id="nPriceTelepolVest" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
						</table>
						
					</fieldset>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<fieldset>
					<legend>Еднократни Такси:</legend>
						
						<table class="input">
							<tr class="odd">
								<td width="330">Цена за Експресна поръчка:</td>
								<td>
									<input type="text" name="nExpressOrderPrice" id="nExpressOrderPrice" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
								</td>
							</tr>
							<tr class="even">
								<td width="330">Цена за Бърза поръчка:</td>
								<td>
									<input type="text" name="nFastOrderPrice" id="nFastOrderPrice" class="inp50" onkeypress="return formatMoney(event);" />&nbsp; лв.
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
	loadXMLDoc2('get');
</script>