{literal}
	<script>
		rpc_debug = true;
		//rpc_html_debug = true;
	
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

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" name="id_person" id="id_person" value="0">
	<input type="hidden" name="id_log_person" id="id_log_person" value="{$nIDLogPerson|default:0}">
	<input type="hidden" name="nIDLimitCard" id="nIDLimitCard" value="{$nIDLimitCard|default:0}">
	<input type="hidden" name="nEarning" id="nEarning" value="0">
	<input type="hidden" name="sRealStart" id="sRealStart" value="{$sRealStart}">
	<input type="hidden" name="sRealEnd" id="sRealEnd" value="{$sRealEnd}">
	<input type="hidden" name="nIDLimitCardOperation" id="nIDLimitCardOperation" value="0">
	
	<table  cellspacing="0" cellpadding="0" style="width:400px;height:100%" border="0" id="filter" >
		<tr>
			<td colspan="2" height="30px;">{include file=personal_card_tabs2.tpl}</td>
		</tr>
		<tr>
			{if $nIDLimitCard}
			<td height="30px;">
				<button onclick="saveOperations();">Запази</button>
			</td>
			{if $sTechRequstType != 'contract'}
			<td align="left" height="30px;">
				<button onclick="editOperation(0);"><img src="images/plus.gif"> Добави </button>
			</td>
			{/if}
			{/if}
		</tr>
		<tr>
			<td colspan="2" valign="top" >
				<div style="width:390px;height:150px;" rpc_excel_panel="off" rpc_resize="off" rpc_paging="off" rpc_autonumber="off" id="result"></div>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="30px;" align="right" >
				<input type="text" name="sEarningLimitCard" id="sEarningLimitCard" style="font-weight:bold;width:100%;text-align:left;" class="clear" readonly>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="30px;" align="right" >
				<input type="text" name="sEarning" id="sEarning" style="font-weight:bold;color:#569457;width:100%;text-align:left;" class="clear" readonly>
			</td>
		</tr>
	</table>
	
</form>

<script>
	onInit();
</script>
