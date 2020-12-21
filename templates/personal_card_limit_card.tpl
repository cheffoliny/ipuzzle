{literal}
	<script>
		rpc_debug = true;
		rpc_method = 'POST';
		
		function onInit() {
			if($('id_limit_card').value != '0') {
				$('id_person').value = parent.$('nID').value;
				
				switch($('type').value) {
					case 'Изграж / Аранж': $('type').style.background = '#5957bb';break;
					case 'Снемане': $('type').style.background = '#bd2937';break;
					case 'Аранжиране': $('type').style.background = '#569457';break;
					case 'Профилактика': $('type').style.background = 'purple';break;
					default: $('type').style.background = '#ff0000';break;
				}
				
				if($('real_end').value == '') {
					if($('real_start').value == '')
						$('start').style.background = '#569457';
					else 
						$('end').style.background = '#bd2937'
				}
			}
			
			loadXMLDoc2('load');
		}
		
		function openObject() {
			
			if($('real_start').value == '') {
				alert('Не е зададен старт на лимитната карта');
			} else if($('real_end').value != '') {
				alert('Лимитната карта е приключена');
			} else {
				id_object = $('id_object').value;
				dialogObjectInfo2('nID='+id_object+'&mobile=1');
			}
		}
		
		function openObjectNew () {
			
			if($('real_start').value == '') {
				alert('Не е зададен старт на лимитната карта');
			} else if($('real_end').value != '') {
				alert('Лимитната карта е приключена');
			} else {
				var id,id_limit_card;
				id = $('id_contract').value;
				id_limit_card = $('id_limit_card').value;
				dialogObjectToLimitCard(id,id_limit_card);
			}
		}
		
		function openRequest (id) {
			if($('real_start').value == '') {
				alert('Не е зададен старт на лимитната карта');
			} else if($('real_end').value != '') {
				alert('Лимитната карта е приключена');
			} else {
				dialogTechRequest(id);
			}
		}
		
		function openContract() {
			loadDirect('export_to_pdf')
			
		}
		
		function realStart() {
			//if($('id_person').value == $('id_log_person').value) {
				if($('real_start').value == '') {
					$('start').style.background = 'silver';
					$('end').style.background = ' #bd2937';
					$('refreshTheOtherIFrames').value = '1';
					loadXMLDoc2('realStart');
				}
			//}
		}
		
		function realEnd() {
			//if($('id_person').value == $('id_log_person').value) {
				if($('real_end').value == '' && $('real_start').value != '') {
					if($('id_object').value == '0') {
						alert('Няма привързан обект към лимитната карта.')
					} else {
						$('earning').value = parent.$('earning').value;
						$('finish_him').value = '1';
						$('refreshTheOtherIFrames').value = '1';
						loadXMLDoc2('operationsDone');
					}
				}
			//}
		}
		
		
		rpc_on_exit = function( nCode )	{
			if( !parseInt( nCode ) ) {
				var id_limit_card
				id_limit_card = $('id_limit_card').value;
					
				if($('refreshTheOtherIFrames').value != '0' ) {
				
					if($('real_end').value != '') {	
						parent.document.getElementById('personal_card_operations').src = 'page.php?page=personal_card_operations&id_limit_card='+id_limit_card;
						parent.document.getElementById('personal_card_schedule').src = 'page.php?page=personal_card_schedule';
					} else {
						if($('finish_him').value == '1') {
							if($('notDoneOperations').value != '0') {
								if(confirm("Има неприключени операции. Желаете ли да затворите лимитната карта въпреки това")) {
									$('end').style.background = 'silver';
									loadXMLDoc2('realEnd');	
								}
							} else {
								$('end').style.background = 'silver';
								loadXMLDoc2('realEnd');	
							}
						} else {
							parent.document.getElementById('personal_card_schedule').src = 'page.php?page=personal_card_schedule';
							parent.document.getElementById('personal_card_operations').src = 'page.php?page=personal_card_operations&id_limit_card='+id_limit_card;
						}
					}
				}
			}
		
		}
		
	</script>
	
	<style>
		button.start {
			width: 60px;


			color: #fefefe;
			font-weight : bold;
			background: silver;
		}
		
		button.end {
			width: 60px;


			color : #fefefe;
			font-weight : bold;
			background: silver;
		}
		
		input.limit_card_name {
		
			color: #fefefe;
			height : 26px;
			padding-left : 6px;
			padding-top: 3px;
			font-size : 16px;
			font-weight : bold;
			border: 0px;
			width: 100%;
			background-color: #617cb3;
		}
		
		input.my_clear {
			border: 0px;
			width: 100%;
			background-color: transparent;
		}

	</style>

{/literal}
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id_person" id="id_person" value="0">
	<input type="hidden" name="id_log_person" id="id_log_person" value="{$nIDLogPerson|default:0}"> 
	<input type="hidden" name="id_limit_card" id="id_limit_card" value="{$nIDLimitCard|default:0}">
	<input type="hidden" name="id_object" id="id_object" value="{$nIDObject|default:0}">
	<input type="hidden" name="num_object" id="num_object" value="{$nNumObject|default:0}">
	<input type="hidden" name="id_contract" id="id_contract" value="{$nIDContract|default:0}">
	<input type="hidden" name="earning" id="earning" value="0">
	<input type="hidden" name="unconfirmed_ppps" id="unconfirmed_ppps" value="0">
	<input type="hidden" name="notDoneOperations" id="notDoneOperations" value="0">
	<input type="hidden" name="finish_him" id="finish_him" value="0">
	<input type="hidden" name="refreshTheOtherIFrames" id="refreshTheOtherIFrames" value="0">
	
	<table class="input" width="100%" border="0">
	{if $nIDLimitCard}

		<tr>
			<td> 
				<input type="text" name="nNum" id="nNum" class="limit_card_name" readonly value="{$num}"  />
			</td>
			<td>
				<input type="text" name="type" id="type" class="limit_card_name" readonly value="{$type}" >
			</td>
		</tr>
			
	</table>
	<table class="input" width="100%" border="0">
		<tr style="height: 5px;" class="odd"><td colspan="4"></td></tr>
		
		{if !$nIDContract}
	

		<tr class="even">
			<td align="right" style=" width:60px;" >
				Задача №
			</td>
			<td style="width:160px;">
				<input style="  width:100%;cursor:pointer;" type="text" name="nRequest" id="nRequest" onclick="openRequest({$id_request});" class="clear" readonly value="{$sRequestNumAndDate}" />
			</td>
			<td rowspan="2" colspan="2">
				<textarea style="height:50px;width:100%;" name="sRequestInfo" id="sRequestInfo" readonly>{$sRequstInfo}</textarea>
			</td>
		</tr>
		<tr class="even">
			<td align="right" style="width:60px;">
				Причина:
			</td>
			<td>
				<input style="  width:100%;" type="text" name="sHoldupReason" id="sHoldupReason" class="clear" readonly value="{$sHoldupReason}" />
			</td>
		</tr>

		{else}

		<tr class="even">
			<td align="right" style=" width:80px;">
				Договор № 
			</td>
			<td colspan="3">
				<input style="  width:100%;cursor:pointer;" type="text" name="$contract_num" id="$contract_num" onclick="openContract();" class="clear" readonly value="{$sContractNumAndData}" />
			</td>
		</tr>
		<tr class="even">
			<td align="right" style=" width:80px;">
				Рекл. сътр. :
			</td>
			<td colspan="3">
				<input style="  width:100%;" type="text" name="$contract_rs" id="$contract_rs" class="clear" readonly value="{$contract_rs}" />
			</td>

		</tr>

		{/if}
		
		<tr style="height: 5px;" class="odd"><td colspan="4"></td></tr>

		<tr class="even">
			
			<td align="right"  style=" width:60px;" >
				Обект:
			</td>
			<td colspan="3">
			{if $nIDObject}
				<a href="javascript:openObject();">
					<input style="  width:100%; cursor:pointer;" type="text" name="sObjName" id="sObjName" class="clear" readonly />
				</a>	
			{else}
				<input style="  width:100%; cursor:pointer; color:red;" type="text" name="sObjName" id="sObjName" class="clear" onclick="openObjectNew()" readonly  />
			{/if}
			</td>
	
		</tr>
		
		<tr class="even">
			<td align="right" style=" width:60px;" >
				Адрес:
			</td>
			<td colspan="3">
				<input style="width:100%;" type="text" name="sObjAddress" id="sObjAddress" class="clear"  readonly />
			</td>
		</tr>
		
		<tr class="even">
			<td align="right" style=" width:60px;" >
				Телефон:
			</td>
			<td colspan="3">
				<input style=" width:100%;" type="text" name="sPhone" id="sPhone" class="clear" readonly />
			</td>
		</tr>
		
		<tr class="even">
		
			<td align="right" style=" width:60px;" >
				МОЛ:
			</td>
			<td colspan="3">
				<input style="width:100%;" type="text" name="sMOL" id="sMOL" class="clear" readonly />
			</td>
		</tr>

		<tr style="height: 5px;" class="odd"><td colspan="4"></td></tr>

		</table>
		<table class="input" width="100%" border="0">
		<tr class="even">
			<td align="right" style="width:80px;">
				План. старт:
			</td>
			<td>
				<input style="width:100%;" type="text" name="planned_start" id="planned_start" class="clear" readonly value="{$planned_start}"/>
			</td>
			<td align="right">
				План. край:
			</td>
			<td>
				<input style="width:100%;" type="text" name="planned_end" id="planned_end" class="clear" readonly value="{$planned_end}"/>
			</td>
		</tr>
		
		
		<tr class="even">
			<td align="right" style="width:80px;">
				<button class="start" type="button" name="start" id="start" onclick="realStart();">Реал. старт</button>
			</td>
			<td >
				<input style="width:100%;" type="text" name="real_start" id="real_start" class="clear" readonly value="{$real_start}"/>
			</td>
			<td align="right">
				<button class="end" type="button" name="end" id="end" onclick="realEnd();">Реал. край</button>
			</td>
			<td>
				<input style="width:100%;" type="text" name="real_end" id="real_end" class="clear" readonly value="{$real_end}"/>
			</td>
			
		</tr>
	
		{/if}
	</table>	
</form>

<script>
	onInit();
</script>