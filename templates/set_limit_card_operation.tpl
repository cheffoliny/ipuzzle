{literal}
<script>
	rpc_debug = true;
	
	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'sName' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestOperations );
			}
		}
	}
			
	function onSuggestOperations ( aParams ) {
		$('id_operation').value = aParams.KEY;
	}
	
	function onInit() {
		$('id_limit_card').value = opener.document.getElementById('nIDLimitCard').value;
		loadXMLDoc2('load');
	}
	
</script>
{/literal}


<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save',3);return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="id_limit_card" name="id_limit_card" value="0">
		<input type="hidden" id="id_operation" name="id_operation" value="0">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} операция</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td>Операция:</td>
				<td>
				{if !$nID}
					<select name="nOperations" id="nOperations" ></select>
				<!--	<input type="text" name="sName" id="sName" suggest="suggest" queryType="tech_operations" class="inp150" /> -->
				{else}
					<input type="text" name="sName" id="sName" class="clear" readonly >
				{/if}	
				</td>
			</tr>
			<tr class="even">
				<td>Количество:</td>
				<td>
					<input type="text" name="nQuantity" id="nQuantity" class="inp50" onkeypress="return formatNumber(event);" >
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
	onInit();
</script>