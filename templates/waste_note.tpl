{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			
			$('pPerson').style.display = "none";
			
			attachEventListener( $('nPersonCode'), "keypress", onKeyPressPersonCode);
			attachEventListener( $('sPersonName'), "keypress", onKeyPressPersonName);
			
			loadXMLDoc2('load');
		}
		
		InitSuggestForm = function() {
			for(var i=0; i<suggest_elements.length; i++) {
				switch( suggest_elements[i]['id'] ) {
					case 'nPersonCode':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
						break;
					case 'sPersonName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
						break;
				}
			}
		}
		
		function onSuggestPerson( aParams ) {
			
			//alert(aParams.KEY);
			var aParts = aParams.KEY.split(';');

			$('nIDPerson').value = 	aParts[0];
			$('nPersonCode').value	 =	aParts[1];
			$('sPersonName').value	 =	aParts[2];
		}
		
		function onKeyPressPersonCode() {
			$('nIDPerson').value = "";
			$('sPersonName').value = "";
		}
		
		function onKeyPressPersonName() {
			$('nIDPerson').value = "";
			$('nPersonCode').value = "";
		}
		
		function onChangeFor(some) {
			if(some.value == 'person') {
				$('pPerson').style.display = "block";
			} else {
				$('pPerson').style.display = "none";
			}
		}
		rpc_on_exit = function( nCode )	{
			if( !parseInt( nCode ) ) {
				if($('nIDPPP').value != '0') {
					opener.document.getElementById('nID').value = $('nIDPPP').value;
				}
			}
		}
		
	</script>
{/literal}



	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3);return false;">
		<input type="hidden" id="nIDAsset" 	name="nIDAsset"	 value="{$nID}" />
		<input type="hidden" id="nIDPPP" 	name="nIDPPP"	 value="{$nIDPPP|default:0}" />
		<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />
		
		<table class="input" border="0">
			<tr>
				<td>
					<div class="page_caption">Бележка по бракуване</div>
				</td>
			</tr>
		</table>

		<table class="input" border="0">
			<tr class="even">
				<td align="right">
					Актив:
				</td>
				<td>
					<input type="text" style="width:300px;" id="sAssetName" name="sAssetName" class="clear" readonly  />
				</td>
			</tr>
			<tr class="odd">
				<td colspan="2">
					<fieldset style="width:360px;height:80px;">
					<legend>Бележка</legend>
					<table>
						<tr>
							<td>
								<textarea style="width:350px;height:50px;" id="sNote" name="sNote" ></textarea>
							</td>
						</tr>
					</table>
					</fieldset>
				</td>
			</tr>
			<tr class="even">
				<td align="right">
					За&nbsp;сметка&nbsp;на:
				</td>
				<td>
					<select style="width:100px;" id="sFor" name="sFor" onchange="onChangeFor(this);">
						<option value="firm">Фирмата</option>
						<option value="person">Служител</option>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td colspan="2" align="center" style="width:360px;height:30px;">
					<div name="pPerson" id="pPerson">
						<input type="text" id="nPersonCode" name="nPersonCode" style="width: 100px; text-align: right;" suggest="suggest" queryType="personByCode" onkeypress="formatDigits( event )" maxlength="12" />&nbsp;
						<input type="text" id="sPersonName" name="sPersonName" style="width: 200px" suggest="suggest" queryType="personByName" />
					</div>
				</td>
			</tr>
			<tr class="even">
				<td colspan="2" style="width:360px;text-align:right;">
					<br><br>
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
	



<script>
	onInit();
</script>