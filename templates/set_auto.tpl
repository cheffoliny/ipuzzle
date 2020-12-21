{literal}
	<script>
		rpc_debug = true;
		function formSubmit()
		{
			var P = window.opener.document.getElementById('nIDFirm').value;
			
			if(P > 0)
			{
				loadXMLDoc2('save',3);
			}
			else
			{
				loadXMLDoc2('save',2);
			}
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="formSubmit();">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} автомобил</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td align="right">Марка:</td>
				<td>
					<select name="nIDMark" id="nIDMark" class="select150" onchange="loadXMLDoc2('loadModels')"/>
				</td>
	
				<td align="right">Модел:</td>
				<td>
					<select name="nIDModel" id="nIDModel" class="select150" />
				</td>
			</tr>
			
			<tr class="even">

				<td align="right">Фирма:</td>
				<td>
					<select name="nIDFirm" id="nIDFirm" class="select150" onchange="loadXMLDoc2('loadOffices')" />
				</td>
			
				<td align="right">Регион:</td>
				<td>
					<select name="nIDOffice" id="nIDOffice" class="select150" onchange="loadXMLDoc2('loadPersons')" />
				</td>
	
			</tr>
			
			<tr class="odd">
	
				<td align="right">Отговорник:</td>
				<td colspan="3">
					<select name="nIDPerson" id="nIDPerson" class="select200" />
				</td>
			</tr>
			
			
			<tr class="even">
				<td align="right">Рег.&nbsp;Номер:</td>
				<td>
					<input type=text name="sRegNum" id="sRegNum" class="inp100" />
				</td>
	
				<td align="right">Цвят:</td>
				<td>
					<input type=text name="sColor" id="sColor" class="inp100" />
				</td>
			</tr>
			
			<tr class="odd">
				<td align="right">Двиг.&nbsp;Номер:</td>
				<td>
					<input type=text name="sDvigatelNum" id="sDvigatelNum" class="inp150" />
				</td>
	
				<td align="right">Рама&nbsp;Номер:</td>
				<td>
					<input type=text name="sRamaNum" id="sRamaNum" class="inp150" />
				</td>
			</tr>
			
			<table class="input">
				<tr>
					<td>
						<fieldset>
							<legend>Разходни норми</legend>
							<table class="input">
								<tr class="odd">
									<td align="right">Лятна Гр:</td>
									<td>
										<input type=text name="sSummerCity" id="sSummerCity" class="inp50" />
									</td>
						
									<td align="right">Лятна извън Гр:</td>
									<td>
										<input type=text name="sSummerOutcity" id="sSummerOutcity" class="inp50" />
									</td>
								</tr>
								
								<tr class="odd">
									<td align="right">Зимна Гр:</td>
									<td>
										<input type=text name="sWinterCity" id="sWinterCity" class="inp50" />
									</td>
						
									<td align="right">Зимна извън Гр:</td>
									<td>
										<input type=text name="sWinterOutcity" id="sWinterOutcity" class="inp50" />
									</td>
								</tr>
							</table>
						</fieldset>
					</td>
				</tr>
			</table>
			
			<tr class="odd">
				<td align="right">Функция:</td>
				<td>
					<select name="nIDFunction" id="nIDFunction" class="select150" />
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
	loadXMLDoc2('load');
</script>