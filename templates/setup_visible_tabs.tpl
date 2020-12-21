{literal}
	<script>
		rpc_debug=true;
		
		var my_action = '';
		
		function formSubmit() {
			my_action = 'save';
			//loadXMLDoc2('save', 3);
			loadXMLDoc2('save', 0);
			
			rpc_on_exit = function() {
				window.opener.loadXMLDoc('load');
				
				rpc_on_exit = function() { };
				close();
			}
			
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редактиране на видими полета{else}Нов шаблон за видими полета{/if}</div>
		
		<div style="height: 5px; font-size: 0px;"></div>
		
		<table class="input">
			<tr class="even">
				<td style="width: 100px;">Наименование:</td>
				<td>
					<input type="text" style="width: 250px;" id="sName" name="sName" />
				</td>
			</tr>
		</table>
		
		<fieldset>
			<legend>Информация:</legend>
			<table class="input">
				<tr class="even">
					<td width="20">
						<input type="checkbox" id="sCode" name="sCode" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>Код</td>
					<td width="20">
						<input type="checkbox" id="sEGN" name="sEGN" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>ЕГН</td>
					<td width="20">
						<input type="checkbox" id="sLK_Num" name="sLK_Num" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>№ на лична карта</td>
				</tr>
				
				<tr class="odd">
					<td width="20">
						<input type="checkbox" id="sPhone" name="sPhone" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>Домашен телефон</td>
					<td width="20">
						<input type="checkbox" id="sBusinessPhone" name="sBusinessPhone" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>Служебен телефон</td>
					<td width="20">
						<input type="checkbox" id="sMobile" name="sMobile" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>Мобилен телефон</td>
				</tr>
				
				<tr class="even">
					<td width="20">
						<input type="checkbox" id="sAddress" name="sAddress" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>Адрес</td>
					<td width="20">
						<input type="checkbox" id="sIBAN" name="sIBAN" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>IBAN</td>
					<td width="20">
						<input type="checkbox" id="sEmail" name="sEmail" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>e-mail</td>
				</tr>
				<tr class="odd">
					<td width="20">
						<input type="checkbox" id="sEducation" name="sEducation" class="clear" {if !$right_private}disabled{/if} />
					</td>
					<td>Образование</td>
					<td colspan="4"></td>
				</tr>
				
				<tr><td colspan="6" style="height: 5px;"></td></tr>
			</table>
		</fieldset>

		<div style="height: 10px; font-size: 0px;"></div>
		
		<fieldset>
			<legend>Служебни данни:</legend>
			<table class="input">
				<tr class="even">
					<td width="20">
						<input type="checkbox" id="sFirm" name="sFirm" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Фирма</td>
					<td width="20">
						<input type="checkbox" id="sObject" name="sObject" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Обект</td>
					<td width="20">
						<input type="checkbox" id="sRegion" name="sRegion" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Регион</td>
				</tr>
				
				<tr class="odd">
					<td width="20">
						<input type="checkbox" id="sPosition" name="sPosition" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Длъжност</td>
					<td width="20">
						<input type="checkbox" id="sDateFrom" name="sDateFrom" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Дата на назначаване</td>
					<td width="20">
						<input type="checkbox" id="sDateVacate" name="sDateVacate" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Дата на напускане</td>
				</tr>
				
				<tr class="even">
					<td width="20">
						<input type="checkbox" id="sPeriod" name="sPeriod" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Прослужено време</td>
					<td width="20">
						<input type="checkbox" id="sStatus" name="sStatus" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Статус</td>
					<td width="20">
						<input type="checkbox" id="sMinSalary" name="sMinSalary" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Мин. осиг. праг</td>
				</tr>
				<tr class="odd">
					<td width="20">
						<input type="checkbox" id="sCipher" name="sCipher" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Шифър</td>
					<td width="20">
						<input type="checkbox" id="sPositionNC" name="sPositionNC" class="clear" {if !$right_office}disabled{/if} />
					</td>
					<td>Длъжност по НКИД</td>
					<td colspan="2"></td>
				</tr>
				
				<tr><td colspan="6" style="height: 5px;"></td></tr>
			</table>
		</fieldset>
		
		<div style="height: 15px;"></div>
		
		<table class="input">
			<tr class="odd">
				<td width="20"><input type="checkbox" id="sDefault" name="sDefault" class="clear" /></td>
				<td width="250">По подразбиране</td>
				<td style="text-align:right;">
					<button type="button" class="search" onclick="formSubmit();"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
</div>

{literal}
	<script>
		loadXMLDoc2('result');
		
//		rpc_on_exit = function( err ) {
//			if( my_action == 'save' && err == 0 ) {
//				if( window.opener && !window.opener.closed ) {
//					window.opener.loadXMLDoc2('result');
//				}
//				
//				my_action = '';
//			}
//		}
	</script>
{/literal}