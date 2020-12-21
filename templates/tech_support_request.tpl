
<form action="" method="POST" name="form1" id="form1"">

<input type="hidden" id="id" name="id" value="0">

<div class="content">			
		<div class="page_caption">Задача за техническо обслужване</div>
		<table class="input" style="margin-top:30px">
			<tr class="even">
				<td width="200">№ </td>
				<td>
					<input type="text" class="inp50" value="234345" style="text-align:right"/>
					/
					<input type="text" class="inp50" value="17.12.2007г." style="width:80px"/>
				</td>
			</tr>
			<tr class="odd">
				<td>Фирма:</td>
				<td>
					<select style="width:220px">
						<option>Инженерингова поддръжка</option>
					</select>
				</td>
			</tr>
			<tr class="even">
				<td>Регион:</td>
				<td>
					<table>
						<tr>
							<td>
								<input type="text" value="9700" style="width:60px"/>
							</td>
							<td>
								<select style="width:153px">
									<option>Шумен</option>
								</select>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="odd">
				<td>Обект:</td>
				<td align="left">
					<table class="input">
						<tr>
							<td style="width:10px;">
								<select id="object_type" style="width:100px">
									<option>СОТ</option>
									<option>ФО</option>
									<option>инженеринг</option>
								</select>
							</td>
							<td align="left">
								<input type="text" style="width:253px"/>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr class="even">
				<td>Тип:</td>
				<td align="left">
					<select style="width:200px">
						<option>профилактика</option>
						<option>изграждане</option>
						<option>снемане</option>
					</select>
				</td>
			</tr>
			
			<tr class="odd">
				<td valign="top">Информация:</td>
				<td>
					<textarea rows="4" style="width:360px">Някаква информация подадена за обслужването на обекта</textarea>
				</td>
			</tr>
			
			<tr class="even">
				<td>Клиент:</td>
				<td align="left">
					<input type="text" style="width:360px"value="Банка Хеброс"/>
				</td>
			</tr>
			
			<tr class="odd">
				<td>Съставил: Диян Миленов Гочев
				<td>&nbsp;</td>
			</tr>
			
		</table>

		<table class="input" style="margin-top:5px;">
			<tr class="odd">
				<td>&nbsp;</td>
				<td style="text-align:right;padding:10px">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Откажи </button>
				</td>
			</tr>
		</table>
		
	</div>
	
</form>