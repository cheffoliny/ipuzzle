{literal}
	<script>
		rpc_debug = true;
		
/*	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'sStreet' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestStreet );
			}		
		}
	}
		
	function onSuggestStreet ( aParams ) {
		$('nIDStreet').value = aParams.KEY;
	}
	*/

	function onStreetChange() {
		$('nIDStreet').value = 0;
	}		
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
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
	
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} склад</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=set_storagehouses_tabs.tpl}</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					<table class="input">
						<tr class="even">
							<td align="right">Име:</td>
							<td>
								<input type=text name="sName" id="sName" style="width: 220px;" />
							</td>
						</tr>
						
						<tr class="odd">
							<td align="right">Фирма:</td>
							<td>
								<select name="nIDFirm" id="nIDFirm" style="width: 220px;" onchange="loadXMLDoc2('loadOffices')" />
							</td>
						</tr>
						
						<tr class="even">
							<td align="right">Регион:</td>
							<td>
								<select name="nIDOffice" id="nIDOffice"  style="width: 220px;" onchange="loadXMLDoc2('loadPersons')" />
							</td>
						</tr>
						
						<tr class="odd">
							<td align="right">МОЛ:</td>
							<td colspan="3">
								<select name="nIDPerson" id="nIDPerson"  style="width: 220px;" />
							</td>
						</tr>
						
						<tr class="odd">
							<td>&nbsp;</td>
							<td colspan="3">&nbsp;</td>
						</tr>
						
						<tr class="even">
							<td align="right">Тип:</td>
							<td colspan="3">
								<select name="sType" id="sType" style="width: 220px;">
									<option value="">-- Не е посочен --</option>
									<option value="new">Нова Техника</option>
									<option value="virtual">Виртуален</option>
									<option value="recik">Рециклирана Техника</option>
									<option value="removed">Свалена Техника</option>
								</select>
							</td>
						</tr>
								
						<table class="input">
							<tr>
								<td>
									<fieldset>
										<legend>Адрес</legend>
										<table class="input">
											<tr class="even">
												<td align="right">Населено място:</td>
												<td>
													<select name="nIDCity" id="nIDCity" onchange="loadXMLDoc2('loadCityAreas')" class="select150" />
												</td>
											</tr>
											
											<tr class="odd">
												<td align="right">Квартал:</td>
												<td>
													<select name="nIDArea" id="nIDArea" class="select150" />
												</td>
											</tr>
											
											<tr class="even">
								
												<td align="right">Улица:</td>
												<td>
													<input type="text" name="sStreet" id="sStreet" class="inp150" suggest="suggest" queryType="sStreet" queryParams="nIDCity" onchange="onStreetChange()"/>
												</td>
									
											</tr>
											<tr class="odd">
												<td align="right">Номер:</td>
												<td>
													<input type=text name="sNumber" id="sNumber" class="inp50" />
													<input type=text name="sOther" id="sOther" style="width: 96px;"  />
												</td>
											</tr>
											
										</table>
									</fieldset>
								</td>
							</tr>
						</table>
						
					</table>
					
					<br />
					<table class="input">
						<tr class="odd">
							<td width="250">&nbsp;</td>
							<td style="text-align:right;">
								<button type="submit" class="search" onclick="formSubmit();"> Запиши </button>
								<button onClick="parent.window.close();"> Затвори </button>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>