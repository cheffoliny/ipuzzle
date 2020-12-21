{literal}
<script>
	rpc_debug = true;

	function isEmpty(aTextField) {
   		if ( (aTextField.value.length == 0) || (aTextField.value == null) ) {
      		return true;
   		}
   		else { return false; }
	}	

	function openWorkCard(id) {
		if ( id > 0 ) {
			window.location.href='page.php?page=working_card_info&nIDCard='+id
		} else {
			window.location.href='page.php?page=working_card_info'
		}
		//dialogWorkingCard(id);
	}
	
	function formSearch() {
		document.getElementById('sAct').value = 'search';
		var sto = document.getElementById('sTo');
		if ( !isEmpty(sto) ) {
			document.getElementById('sStatus').value = 'all';
			alert('Статуса ще бъде игнориран!');
		}
		loadXMLDoc2('result');
	}
	
	
</script>
{/literal}

<dlcalendar click_element_id="calFrom" input_element_id="sFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="calTo" input_element_id="sTo" tool_tip="Изберете дата"></dlcalendar>

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	<input type="hidden" name="sAct" id="sAct" value="load" />
	<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />		
	
	<div class="page_caption" id="capt" name="capt">Справка "Работни карти"</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
		<tr>
			<td>{include file=working_card_tabs.tpl}</td>
		</tr>

	</table>
			
	<table class="page_data">
		<tr>
			<td class="buttons">
				{if $right_edit}<button onclick="openWorkCard(0);" class="search"><img src="images/plus.gif"> Текуща РК </button>{else}&nbsp;{/if}
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Номер</td>
				<td align="left" style="width: 120px;">
					<input name="nNum" id="nNum" type="text" style="width: 100px;" onkeypress="return formatDigits(event);" />
				</td>
				<td align="right">Статус</td>
				<td style="width: 120px;" align="left">
					<select id="sStatus" name="sStatus" style="width: 100px;" >
						<option value="all">Всички</option>
						<option value="active">Активни</option>
						<option value="inactive">Неактивни</option>
					</select>
				</td>
				<td align="right">от</td>
				<td align="left">
					<input name="sFrom" type="text" id="sFrom" style="width: 80px;" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" value="{$sSevenDaysBefore}" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="calFrom" />&nbsp;&nbsp;&nbsp;
				</td>
				<td align="right">до</td>
				<td align="left">
					<input name="sTo" type="text" id="sTo" style="width: 80px;" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;
					<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="calTo" />
				</td>
			</tr><tr>	
				<td align="right">Диспечер</td>
				<td align="left" colspan="6">
					<select id="nIDDispatcher" name="nIDDispatcher" style="width: 405px;" >
						<option value="0">Всички</option>
					</select>
				</td>
						
				<td align="right"><button type="button" name="Button" class="search" onClick="formSearch();" ><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2('result');				
</script>