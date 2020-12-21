{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			
			if(document.getElementById('nIDConfirmUser').value != '0') {
				var sPPPType = $('sPPPType').value
								
				$('bAssetSource').setAttribute('disabled','disabled');
				
				if( sPPPType == 'enter') {
					$('nIDStoragehouse').setAttribute('disabled','disabled');
					$('bNewAsset').setAttribute('disabled','disabled');
				} else if(sPPPType == 'attach') {
					$('bNewAsset').setAttribute('disabled','disabled');
					$('sDestType').setAttribute('disabled','disabled');
					$('nPersonCode').setAttribute('disabled','disabled');
					$('sPersonName').setAttribute('disabled','disabled');
					$('pAssetDest').setAttribute('disabled','disabled');
					$('bAssetDest').setAttribute('disabled','disabled');
				} 
				
				$('bAddAsset').setAttribute('disabled','disabled');
				$('confirmed').setAttribute('disabled','disabled');
				$('save').setAttribute('disabled','disabled');
				
			}
			
			if($('sPPPType').value == 'attach') {
				attachEventListener( $('nPersonCode'), "keypress", onKeyPressPersonCode);
				attachEventListener( $('sPersonName'), "keypress", onKeyPressPersonName);
			}
			
			loadXMLDoc2('result');
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
			
			var aParts = aParams.KEY.split(';');

			$('nIDPersonDest').value = 	aParts[0];
			$('nPersonCode').value	 =	aParts[1];
			$('sPersonName').value	 =	aParts[2];
		}
		
		function onKeyPressPersonCode() {
			$('nIDPersonDest').value = "";
			$('sPersonName').value = "";
		}
		
		function onKeyPressPersonName() {
			$('nIDPersonDest').value = "";
			$('nPersonCode').value = "";
		}
		
		function delAsset(id) {
			var aParts = id.split(',');
			//alert(aParts[1]);
			$('nIDAssetToDel').value = aParts[1];
			loadXMLDoc2('del_asset',1);
		}
		
		function openAssetSearch(type) {
			var nIDFirmSearch = $('nIDFirmSearch').value;
			var nIDOfficeSearch = $('nIDOfficeSearch').value;
			var nIDPersonSearch = $('nIDPersonSearch').value;
			var nIDGroupSearch = $('nIDGroupSearch').value;
			
			var sParams =	"type="+type+
							"&nIDFirm="+nIDFirmSearch+
							"&nIDOffice="+nIDOfficeSearch+
							"&nIDPerson="+nIDPersonSearch+
							"&nIDGroup="+nIDGroupSearch; 
			//alert(sParams);
			dialogAssetSearch(sParams);
		}

		function openAssetInfo(id) {
			var aParts = id.split(',');
			dialogAssetInfo(aParts[0]);
		}
		
		function addAsset() {
			if($('sAssetSourcePriceLeft').value != '0' && $('sPPPType').value == 'waste') {
				dialogWasteNote( $('nIDAssetSource').value,$('nID').value ); 
			} else {
				loadXMLDoc2('add_asset');
			}
		}
		
		function onchangeDest(dest) {
			switch(dest.value) {
				case 'person': 
					$('pPerson').style.display = 'block';
					$('pAssetDest').style.display = 'none';
					$('pAssetDestButton').style.display = 'none';
					break;
					
				case 'asset': 
					$('pPerson').style.display = 'none';
					$('pAssetDest').style.display = 'block';
					$('pAssetDestButton').style.display = 'block';
					break;
			}
		}
		
		/*rpc_on_exit = function(nCode) {
			
			if(!parseInt(nCode)) {
				$('nIDStoragehouse').focus();
				if($('refreshPage').value == 'yes') {
					var nIDPPP = $('nID').value;
					var sPPPType = $('sPPPType').value;
					window.location = 'http://localhost/telenet/page.php?page=assets_ppp&id='+nIDPPP+'&type='+sPPPType;
				}
			}
		}*/
	</script>
{/literal}


<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save',3);return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDAssetToDel" name="nIDAssetToDel" value="0">
		<input type="hidden" id="nIDAssetSource" name="nIDAssetSource" value="0">
		<input type="hidden" id="sAssetSourcePriceLeft" name="sAssetSourcePriceLeft" value="0">
		<input type="hidden" id="nIDAssetDest" name="nIDAssetDest" value="0">
		<input type="hidden" id="nIDPersonDest" name="nIDPersonDest" value="0">
		<input type="hidden" id="nIDConfirmUser" name="nIDConfirmUser" value="{$nIDConfirmUser|default:0}">
		 
		<input type="hidden" id="sPPPType" name="sPPPType" value="{$sPPPType}">

		<!-- последно селектнати опции на формата "Търсене на актив" -->
		<input type="hidden" id="nIDFirmSearch" name="nIDFirmSearch" value="0">
		<input type="hidden" id="nIDOfficeSearch" name="nIDOfficeSearch" value="0">
		<input type="hidden" id="nIDPersonSearch" name="nIDPersonSearch" value="0">
		<input type="hidden" id="nIDGroupSearch" name="nIDGroupSearch" value="0">
		
		<input type="hidden" id="refreshPage" name="refreshPage" value="no">
		
		<table class="input" border="0">
			<tr>
				<td>
					<div class="page_caption">{if $nID}Редакция на ППП {$sNum} {else}Ново ППП{/if}</div>
				</td>
			</tr>
		</table>
		

		
		<center>
			<table class="search" border="0" style="width:100%;">
				<tr>
					<td style="width:100px;">
						<button name="bAssetSource" id="bAssetSource" class="search" onClick="openAssetSearch('to_source');"> Актив </button>
					</td>
					<td style="width:210px;">
						<input type="text" style="width:200px;" id="sAssetSource" name="sAssetSource"  readonly/>
					</td>
					
					{if $sPPPType eq 'enter'}
					
					<td>
						<button name="bNewAsset" id="bNewAsset" onclick="openAssetInfo('0,0');"> Нов Актив </button>
					</td>
					
					<td style="width:120px;text-align:right;">
						Приемащ:&nbsp;
					</td>
					<td align="left">
						<select class="default" id="nIDStoragehouse" name="nIDStoragehouse"/>
					</td>

					<td>
						Количество
					</td>
					<td>
						<input type="text" style="width:30px;" name="nCount" id="nCount" onkeypress="return formatNumber(event);" value="1"/>
					</td>
					
					{elseif $sPPPType eq 'attach'}
					
					<td>
						<button name="bNewAsset" id="bNewAsset" onclick="openAssetInfo('0,0');"> Нов Актив </button>
					</td>
					
					<td style="width:100px;text-align:right;">
						Приемащ:&nbsp;
					</td>
					<td style="width:100px;">
						<select name="sDestType" id="sDestType" style="width:100px;" onchange="onchangeDest(this);">
							<option value="person">Служител</option>
							<option value="asset">Актив</option>
						</select>
					</td>
					<td>
						<div name="pPerson" id="pPerson">
							<input type="text" id="nPersonCode" name="nPersonCode" style="width: 100px; text-align: right;" suggest="suggest" queryType="personByCode" onkeypress="formatDigits( event )" maxlength="12" />&nbsp;
							<input type="text" id="sPersonName" name="sPersonName" style="width: 200px" suggest="suggest" queryType="personByName" />
						</div>
						<div name="pAssetDest" id="pAssetDest" style="display:none;">
							<input type="text" name="sAssetDest" id="sAssetDest" style="width:200px;" readonly />
						</div>
					</td>
					<td align="left">
						<div name="pAssetDestButton" id="pAssetDestButton" style="display:none;">
							<button name="bAssetDest" id="bAssetDest" class="search" onClick="openAssetSearch('to_dest');"> Актив </button>
						</div>
					</td>

					{elseif $sPPPType eq 'waste'}
					
					<td>
						&nbsp;
					</td>
					
					{/if}
					<td style="width: 100px;">
						<button name="bAddAsset" id="bAddAsset" class="search" onClick="addAsset();"> Добави </button>
					</td>
				</tr>
			</table>
		</center>
		<hr>
		
		<div id="result" rpc_excel_panel="off" rpc_resize="off" style="height: 370px; overflow: auto;"></div>

		<table class="input" border="0">
			<tr class="odd">
				<td>
					<input type="checkbox" name="confirmed" id="confirmed" class="clear">&nbsp;Потвърден
				</td>
				<td class="export">
					<button class="btn btn-xs btn-info" onclick="loadDirect('export_to_pdf');">
                        <i class="fa fa-file-pdf-o"></i>
						PDF
					</button>
				</td>
				<td style="text-align:right;">
					<button name="save" id="save" type="submit" class="search">Запиши</button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
	

</div>

<script>
	onInit();
</script>