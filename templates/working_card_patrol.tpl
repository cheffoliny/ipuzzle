{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

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
			document.getElementById('capt').className += ' ui-patrol-card-closed';
		} else {
			document.getElementById('capt').className += ' ui-patrol-card-open';
		}
	}
	
</script>
{/literal}

<div class="ui-patrol-report">
<form name="form1" id="form1" class="ui-nomenclature-list ui-technical-list ui-working-card-patrol" onsubmit="return false;">
	<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />
	<input type="hidden" id="isLOCK" name="isLOCK" value="no" />
	<input type="hidden" id="locked" name="locked" value="{$locked|default:0}" />

	<div class="page_caption" id="capt" name="capt">Автопатрули към работна карта № {$nIDCard} {if $locked}[ПРИКЛЮЧЕНА!]{/if}</div>
	
	<div class="ui-patrol-tabs" id="filter">{include file="working_card_tabs.tpl"}</div>
	<div class="ui-patrol-toolbar">
		<button type="button" onclick="editPatrol(0);" class="btn btn-primary" id="Add" {if $locked}disabled{/if}><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>
	</div>
	
	<center class="ui-nomenclature-filter-wrap ui-technical-filter-wrap">
		<table class="search ui-nomenclature-filter ui-technical-filter">
			<tr>
				<td align="right">Регион:&nbsp;</td>
				<td align="left">
					<select id="nRegion" name="nRegion" style="width: 300px;" >
						<option value="0">Всички</option>
					</select>
				</td>
				<td align="right"><button type="button" name="Button" class="search" onClick="formSearch();" {if $locked}disabled{/if}><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result" class="ui-technical-result ui-patrol-result"
		rpc_resize="off"></div>

</form>
</div>

<script>
	formLoad();
	loadXMLDoc2('result');
	
	{if !$right_edit}
		$('Add').setAttribute( 'disabled', 'disabled' );
	{/if}
		
</script>
