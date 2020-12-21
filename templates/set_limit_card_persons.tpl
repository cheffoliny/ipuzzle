{literal}
<script>
	rpc_debug = true;
	
	function formChange(type) {
		if ( type == 'firm' ) {
			document.getElementById('nIDOffice').value = 0;
		}
		loadXMLDoc2('load', 0);
	}	
	
	function formSubmit() {
		loadXMLDoc2('save', 0);
		
		rpc_on_exit = function() {
			if ( typeof(window.opener.test) != 'undefined' ) {
				window.opener.test();	
			}
			//window.opener.window.reload();
			
			rpc_on_exit = function() {};
			
			parent.window.close();
		}
	}
		
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard}">

		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} СЛУЖИТЕЛ</div>

		<fieldset>
			<legend>Информация за служител</legend>

			<table class="input">
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
				
				<tr class="even">
					<td>фирма:</td>
					<td>
						<select name="nIDFirm" id="nIDFirm" style="width: 240px;" onChange="formChange('firm');" ></select>
					</td>
				</tr>

				<tr class="even">
					<td>Регион:</td>
					<td>
						<select name="nIDOffice" id="nIDOffice" style="width: 240px;" onChange="formChange('office');" ></select>
					</td>
				</tr>
				
				<tr class="even">
					<td>Служител:</td>
					<td>
						<select name="nIDPerson" id="nIDPerson" style="width: 240px;" ></select>
					</td>
				</tr>

				<tr class="even">
					<td>Процент:</td>
					<td>
						<input type="text" name="nPercent" id="nPercent" style="width: 40px;" maxlength="3" onkeydown="return formatNumber(event);" />
						&nbsp;&nbsp;&nbsp;<input type="checkbox" id="all" name="all" class="clear" onClick="formChange('office')" />&nbsp Всички служители
					</td>
				</tr>

			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
		</table>
		
		</fieldset>
		
		<div style="height: 10px;"></div>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" class="search" onClick="formSubmit();"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>