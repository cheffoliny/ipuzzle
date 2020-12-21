{literal}
<script>
	rpc_debug = true;

	function isEmpty(aTextField) {
   		if ( (aTextField.value.length == 0) || (aTextField.value == null) ) {
      		return true;
   		}
   		else { return false; }
	}	

	function editPatrol(id) {
		var idc = document.getElementById('nIDCard').value;
		if ( document.getElementById('isLOCK').value == 'no' ) {
			dialogPatrol(id, idc);
		} else alert('Работната карта е затворена!');
	}
	
	function formSearch() {
		loadXMLDoc2('result');
	}
	
	function stopRoadList(id) {
		dialogStopRoadList(id);
	}

	function openFuelList(id) {
		dialgOpenFuelList(id);
	}
	
	function formLoad() {
		if ( document.getElementById('locked').value == 1 ) {
			document.getElementById('capt').style.background = 'red';
		} else {
			document.getElementById('capt').style.background = 'green';
		}
	}
	
</script>
{/literal}

<div>
<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />
	<input type="hidden" id="isLOCK" name="isLOCK" value="no" />
	<input type="hidden" id="locked" name="locked" value="{$locked|default:0}" />

	<div class="page_caption" id="capt" name="capt">Автопатрули към работна карта № {$nIDCard} {if $locked}[ПРИКЛЮЧЕНА!]{/if}</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
		<tr>
			<td>{include file=working_card_tabs.tpl}</td>
		</tr>
		<tr>
			<td>
				<center>
					<table class="page_data" style="width:100%">
						<tr>
							<td valign="top" align="right" class="buttons">
								<button onclick="editPatrol(0);" class="search" id="Add" {if $locked}disabled{/if}><img src="images/plus.gif"> Добави </button>
							</td>
						</tr>
					</table>
				</center>
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Регион:&nbsp;</td>
				<td align="left">
					<select id="nRegion" name="nRegion" style="width: 300px;" >
						<option value="0">Всички</option>
					</select>
				</td>
				<td align="right"><button type="button" name="Button" class="search" onClick="formSearch();" {if $locked}disabled{/if}><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result"></div>

</form>
</div>

<script>
	formLoad();
	loadXMLDoc2('result');
	
	{if !$right_edit}
		$('Add').setAttribute( 'disabled', 'disabled' );
	{/if}
		
</script>