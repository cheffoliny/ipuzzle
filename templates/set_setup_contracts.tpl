{literal}
<script>
	rpc_debug = true;
	
	function checkNow(name) {

		if ( name == 'chMonth' ) {
			$('chMonth').checked = 'on';
		} else if ( name == 'chSingle' ) {
			$('chSingle').checked = 'on';
		}
	}
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция{else}Добавяне{/if}</div>
		<br />

		<table class="input">
			
			<tr class="even">
				<td>
					<fieldset>
					<legend>Месечни Такси:</legend>
						
						<table class="input">
						
							<tr class="odd">
								<td width="330">Код:</td>
								<td>
									<input type="text" name="sCode" id="sCode" style="width: 300px;" tabindex="10" />&nbsp;
								</td>
							</tr>
							
							<tr class="even">
								<td width="330">Наименование:</td>
								<td>
									<input type="text" name="sName" id="sName" style="width: 300px;" tabindex="1" />&nbsp;
								</td>
							</tr>
							
							<tr class="odd">
								<td width="330">Услуга:</td>
								<td>
									<select id="nIDService" name="nIDService" style="width: 300px;" tabindex="2" ></select>&nbsp;
								</td>
							</tr>
							
							<tr>
								<td colspan="2" style="font-size: 0px; height: 5px;"></td>
							</tr>
							
						</table>
						
					</fieldset>
				</td>
			</tr>
			
			<tr class="odd">
				<td>
					<fieldset>
					<legend>Тип на услугата:</legend>
						
						<table class="input">
						
							<tr class="odd">
								<td width="30">
									<input type="radio" id="chMonth" name="cType" value="chMonth" class="clear" tabindex="3" />
								</td>
								<td onclick="checkNow('chMonth');" style="cursor: pointer;">Тип месечно (периодично) задължение</td>
							</tr>
							
							<tr class="even">
								<td width="30">
									<input type="radio" id="chSingle" name="cType" value="chSingle" class="clear" tabindex="4" />
								</td>							
								<td onclick="checkNow('chSingle');" style="cursor: pointer;">Тип еднократно задължение</td>
							</tr>
							
							<tr>
								<td colspan="2" style="font-size: 0px; height: 5px;"></td>
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
					<button type="submit" class="search" tabindex="5"> Запиши </button>
					<button onClick="parent.window.close();" tabindex="6" > Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('get');
	
	$('sName').focus();
</script>