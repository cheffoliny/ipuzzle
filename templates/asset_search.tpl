{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('load');
			//destination
			if($('sTypeTo').value != "to_source")
			{
				document.getElementById('nIDDest').value = 0;
				var nIDSource = document.getElementById('nIDSource');
				nIDSource.value = opener.document.getElementById('nIDAssetSource').value;
			}
			//source
			else
			{
				document.getElementById('nIDSource').value = 0;
				var nIDDest = document.getElementById('nIDDest');
				nIDDest.value = opener.document.getElementById('nIDAssetDest').value;
			}

		}
		
		function getResult() {
			loadXMLDoc2('result');
			
		}
		
		function openAssetInfo(id) {
			var aParts = id.split(',');
			dialogAssetInfo(aParts[0]);
		}
		
		function transfer(id) {
			var aAsset = new Array();
			aAsset = id.split(',');
			if(typeof(aAsset[2]) == 'undefined')aAsset[2] = '0';
			
			sTypeTo = $('sTypeTo').value;
			
			opener.document.getElementById('nIDFirmSearch').value = $('nIDFirm').value;
			opener.document.getElementById('nIDOfficeSearch').value = $('nIDOffice').value;
			opener.document.getElementById('nIDPersonSearch').value = $('nIDPerson').value;
			opener.document.getElementById('nIDGroupSearch').value = $('nIDGroup').value;
			
			if( sTypeTo == "to_source") {
				opener.document.getElementById('nIDAssetSource').value = aAsset[0];
				opener.document.getElementById('sAssetSource').value = aAsset[1];
				opener.document.getElementById('sAssetSourcePriceLeft').value = aAsset[2];
			} else {
				opener.document.getElementById('nIDAssetDest').value = aAsset[0];
				opener.document.getElementById('sAssetDest').value = aAsset[1];
			}
			window.close();
		}
		
	</script>
{/literal}



	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3);return false;">
	<input type="hidden" id="sTypeTo" name="sTypeTo" value="{$sTypeTo}">
	
	<input type="hidden" id="nIDFirmS" name="nIDFirmS" value="{$nIDFirm|default:0}">
	<input type="hidden" id="nIDOfficeS" name="nIDOfficeS" value="{$nIDOffice|default:0}">
	<input type="hidden" id="nIDPersonS" name="nIDPersonS" value="{$nIDPerson|default:0}">
	<input type="hidden" id="nIDGroupS" name="nIDGroupS" value="{$nIDGroup|default:0}">
	<input type="hidden" id="nIDSource" name="nIDSource">
	<input type="hidden" id="nIDDest" name="nIDDest">
	
		<table class="input" border="0">
			<tr>
				<td>
					<div class="page_caption">Търсене на актив</div>
				</td>
			</tr>
		</table>

		<table class="input">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" onchange="loadXMLDoc2('loadPersons')" />
				</td>
				<td>
					Номер&nbsp;<input style="width:100px;" type="text" id="nNum" name="nNum" />
				</td>
			</tr>
			<tr>
				<td align="right">Служители</td>
				<td>
					<select class="default" name="nIDPerson" id="nIDPerson" />
				</td>
				<td align="right">Група</td>
				<td>
					<select class="default" name="nIDGroup" id="nIDGroup" />
				</td>
				<td>
					<button name="Button" onclick="getResult();"><img src="images/confirm.gif">Търси</button>
				</td>
			</tr>
		</table>
		
		<table class="input" width="100%" border="0">
			<tr>
				<td>
					<div id="result" rpc_excel_panel="off" rpc_autonumber="off" rpc_resize="off"  style="height: 370px; overflow: auto;"></div>
				</td>
			</tr>
		</table>
		
	</form>
	



<script>
	onInit();
</script>