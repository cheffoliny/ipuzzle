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
	

	function onStreetChange() {
		$('nIDStreet').value = 0;
	}		
	*/	function formSubmit()
		{
			loadXMLDoc2('save',3);
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
	
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} склад</div>
		<br />
		
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
			
			<tr class="even">
				<td align="right">Регион:</td>
				<td>
					<select name="nIDOffice" id="nIDOffice" style="width: 220px;" onchange="loadXMLDoc2('loadPersons')" />
				</td>		
						
			<tr class="odd">
	
				<td align="right">МОЛ:</td>
				<td colspan="3">
					<select name="nIDPerson" id="nIDPerson"  style="width: 220px;" />
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
		
	</form>
</div>
		
<script>
	loadXMLDoc2('load');
</script>