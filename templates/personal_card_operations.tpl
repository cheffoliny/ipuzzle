{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
	
		function onInit() {
			$('id_person').value = parent.$('nID').value;
			if($('nIDLimitCard').value != '0')
			loadXMLDoc2('result');
		}
		
		function delOperation(id) {
			
			//if($('id_person').value == $('id_log_person').value) {
			
				if($('sRealStart').value == "0000-00-00 00:00:00") {
					alert("Не е зададено начало на лимитната карта");
				} else if($('sRealEnd').value != "0000-00-00 00:00:00") {
					alert("Лимитната карта е затворена");
				} else {
					$('nIDLimitCardOperation').value = id;
					if(confirm('Наистина ли желаете да премахнете операцията'))
					loadXMLDoc2('delete',1);
				}
				
			//}
		}
		
		function editOperation(id) {
			
			//if($('id_person').value == $('id_log_person').value) {
			
				if($('sRealStart').value == "0000-00-00 00:00:00") {
					alert("Не е зададено начало на лимитната карта");
				} else if($('sRealEnd').value != "0000-00-00 00:00:00") {
					alert("Лимитната карта е затворена");
				} else {
					dialogSetLimitCardOperation( id )
				}
				
			//}
		}
		
		function saveOperations() {
			
			//if($('id_person').value == $('id_log_person').value) {
			
				if($('sRealStart').value == "0000-00-00 00:00:00") {
					alert("Не е зададено начало на лимитната карта");
				} else if($('sRealEnd').value != "0000-00-00 00:00:00") {
					alert("Лимитната карта е затворена");
				} else {
					loadXMLDoc2('save',1);
				}
			
			//}
		}
		
		function confirmOperation(id) {
			
			//if($('id_person').value == $('id_log_person').value) {
			
				if($('sRealStart').value == "0000-00-00 00:00:00") {
					alert("Не е зададено начало на лимитната карта");
				} else if($('sRealEnd').value != "0000-00-00 00:00:00") {
					alert("Лимитната карта е затворена");
				} else {
					$('nIDLimitCardOperation').value = id;
					loadXMLDoc2('confirm',1);
				}
			
			//}
		}
		
		function unConfirmOperation(id) {
			
			//if($('id_person').value == $('id_log_person').value) {
			
				if($('sRealStart').value == "0000-00-00 00:00:00") {
					alert("Не е зададено начало на лимитната карта");
				} else if($('sRealEnd').value != "0000-00-00 00:00:00") {
					alert("Лимитната карта е затворена");
				} else {
					$('nIDLimitCardOperation').value = id;
					loadXMLDoc2('unconfirm',1);
				}
			
			//}
		}
		
		rpc_on_exit = function( nCode )	{
			if( !parseInt( nCode ) ) {
				parent.document.getElementById( 'earning' ).value = $('nEarning').value;;
			}
		}
	</script>
	
{/literal}

<form name="form1" id="form1" class="ui-personal-card-subview ui-personal-card-operations" onsubmit="return false;">
	<input type="hidden" name="id_person" id="id_person" value="0">
	<input type="hidden" name="id_log_person" id="id_log_person" value="{$nIDLogPerson|default:0}">
	<input type="hidden" name="nIDLimitCard" id="nIDLimitCard" value="{$nIDLimitCard|default:0}">
	<input type="hidden" name="nEarning" id="nEarning" value="0">
	<input type="hidden" name="sRealStart" id="sRealStart" value="{$sRealStart}">
	<input type="hidden" name="sRealEnd" id="sRealEnd" value="{$sRealEnd}">
	<input type="hidden" name="nIDLimitCardOperation" id="nIDLimitCardOperation" value="0">
	
	{include file="personal_card_tabs2.tpl"}

	{if $nIDLimitCard}
		<div class="ui-personal-card-toolbar" role="toolbar" aria-label="Операции по лимитна карта">
			<button type="button" class="btn btn-success" onclick="saveOperations();">
				<span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запази
			</button>
			{if $sTechRequstType != 'contract'}
				<button type="button" class="btn btn-primary" onclick="editOperation(0);">
					<span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави
				</button>
			{/if}
		</div>
	{/if}

	<div class="ui-personal-card-result"
		rpc_excel_panel="off"
		rpc_resize="off"
		rpc_paging="off"
		rpc_autonumber="off"
		id="result"></div>

	<div class="ui-personal-card-summary">
		<label for="sEarningLimitCard">Начисление по лимитна карта</label>
		<input type="text" name="sEarningLimitCard" id="sEarningLimitCard" class="form-control" readonly>
		<label for="sEarning">Общо начисление</label>
		<input type="text" name="sEarning" id="sEarning" class="form-control ui-personal-card-summary-total" readonly>
	</div>
	
</form>

<script>
	onInit();
</script>
