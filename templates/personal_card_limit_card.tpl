{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
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
<form action="" name="form1" id="form1" class="ui-personal-card-subview ui-personal-card-limit-card" onSubmit="return false;">
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
	
	{if $nIDLimitCard}
		<header class="ui-personal-card-limit-header">
			<input type="text" name="nNum" id="nNum" class="form-control limit_card_name" readonly value="{$num}" aria-label="Номер на лимитна карта" />
			<input type="text" name="type" id="type" class="form-control limit_card_name" readonly value="{$type}" aria-label="Тип на лимитната карта" />
		</header>

		<section class="ui-personal-card-fields" aria-label="Данни за задачата и обекта">
		{if !$nIDContract}
			<label for="nRequest"><span class="ui-icon ui-icon-clipboard" aria-hidden="true"></span> Задача №</label>
			<input type="text" name="nRequest" id="nRequest" onclick="openRequest({$id_request});" class="form-control ui-personal-card-link-field" readonly value="{$sRequestNumAndDate}" />
			<label for="sHoldupReason"><span class="ui-icon ui-icon-info" aria-hidden="true"></span> Причина</label>
			<input type="text" name="sHoldupReason" id="sHoldupReason" class="form-control" readonly value="{$sHoldupReason}" />
			<label for="sRequestInfo" class="ui-personal-card-field-wide"><span class="ui-icon ui-icon-document" aria-hidden="true"></span> Информация за задачата</label>
			<textarea name="sRequestInfo" id="sRequestInfo" class="form-control ui-personal-card-field-wide" readonly>{$sRequstInfo}</textarea>
		{else}
			<label for="contract_num"><span class="ui-icon ui-icon-contract" aria-hidden="true"></span> Договор №</label>
			<input type="text" name="contract_num" id="contract_num" onclick="openContract();" class="form-control ui-personal-card-link-field" readonly value="{$sContractNumAndData}" />
			<label for="contract_rs"><span class="ui-icon ui-icon-user" aria-hidden="true"></span> Рекл. сътр.</label>
			<input type="text" name="contract_rs" id="contract_rs" class="form-control" readonly value="{$contract_rs}" />
		{/if}

			<label for="sObjName"><span class="ui-icon ui-icon-home" aria-hidden="true"></span> Обект</label>
			{if $nIDObject}
				<input type="text" name="sObjName" id="sObjName" class="form-control ui-personal-card-link-field" onclick="openObject();" readonly />
			{else}
				<input type="text" name="sObjName" id="sObjName" class="form-control ui-personal-card-link-field ui-personal-card-missing" onclick="openObjectNew();" readonly />
			{/if}

			<label for="sObjAddress"><span class="ui-icon ui-icon-location" aria-hidden="true"></span> Адрес</label>
			<input type="text" name="sObjAddress" id="sObjAddress" class="form-control" readonly />
			<label for="sPhone"><span class="ui-icon ui-icon-phone" aria-hidden="true"></span> Телефон</label>
			<input type="text" name="sPhone" id="sPhone" class="form-control" readonly />
			<label for="sMOL"><span class="ui-icon ui-icon-id-card" aria-hidden="true"></span> МОЛ</label>
			<input type="text" name="sMOL" id="sMOL" class="form-control" readonly />
		</section>

		<section class="ui-personal-card-timeline" aria-label="Планирани и реални периоди">
			<label for="planned_start">План. старт</label>
			<input type="text" name="planned_start" id="planned_start" class="form-control" readonly value="{$planned_start}" />
			<label for="planned_end">План. край</label>
			<input type="text" name="planned_end" id="planned_end" class="form-control" readonly value="{$planned_end}" />
			<button class="btn btn-success start" type="button" name="start" id="start" onclick="realStart();">
				<span class="ui-icon ui-icon-play" aria-hidden="true"></span> Реал. старт
			</button>
			<input type="text" name="real_start" id="real_start" class="form-control" readonly value="{$real_start}" />
			<button class="btn btn-danger end" type="button" name="end" id="end" onclick="realEnd();">
				<span class="ui-icon ui-icon-stop" aria-hidden="true"></span> Реал. край
			</button>
			<input type="text" name="real_end" id="real_end" class="form-control" readonly value="{$real_end}" />
		</section>
	{/if}
</form>

<script>
	onInit();
</script>
