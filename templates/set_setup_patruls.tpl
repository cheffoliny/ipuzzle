{literal}
<script>
	rpc_debug = true;
	
	function onInit()
	{
		loadXMLDoc2('load');
	}
	
	function onChangeOffice()
	{
		loadXMLDoc2('getPatruls');
	}
	function formSubmit()
	{
		var P = window.opener.document.getElementById('nIDFirm').value;
		
		if(P > 0)
		{
			loadXMLDoc2('save');
		}
		else
		{
			loadXMLDoc2('save',2);
		}
	}
			
</script>
{/literal}

<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-operational-dialog ui-patruls-dialog" onsubmit="formSubmit();return false">
	
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">Редакция на позивна</div>

		<table class="input ui-nomenclature-form">
		
			<tr class="odd">
				<td width="100">Фирма:</td>
				<td>
					<select name="nIDFirm" id="nIDFirm" style="width: 240px;" onchange="loadXMLDoc2('loadOffices')"></select>
				</td>
			</tr>
		
			<tr class="even">
				<td width="100">Регион:</td>
				<td>
					<select name="nIDOffice" id="nIDOffice" style="width: 240px;" onchange="onChangeOffice()"></select>
				</td>
			</tr>
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr> 
		</table>
		
		<fieldset class="ui-nomenclature-fieldset">
			<legend>Патрули към този регион</legend>
			<table class="input ui-nomenclature-form">
				<tr class="even">
					<td align="center">
						<textarea name="sPatruls" id="sPatruls" style="width: 325px; height: 80px;" /></textarea>
					</td>
				</tr>
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			</table>
		</fieldset>
		
		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	onInit();
</script>
