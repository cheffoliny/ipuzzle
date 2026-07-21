{literal}
	<script>
		rpc_debug = true;
		
		function IsEmpty(aTextField) {
			if ( (aTextField.value.length==0) || (aTextField.value==null) ) {
					return true;
			} else { return false; }
		}	
		
		function submit_form() {
			var mname = document.getElementById('mname');
			var lname = document.getElementById('lname');
			var egn = document.getElementById('egn');
			var addr_city = document.getElementById('addr_city');
			var addr_street = document.getElementById('addr_street');
			var addr_num = document.getElementById('addr_num');
			
			if ( IsEmpty(mname) || IsEmpty(lname) || IsEmpty(egn) || IsEmpty(addr_city) || IsEmpty(addr_street) || IsEmpty(addr_num) ) { 
				if ( confirm('Има непопълнени полета. Желаете ли да продължите?') ) {
					loadXMLDoc( 'save', 0 );
				}
			} else loadXMLDoc( 'save', 0 );
		}
		
		function update_image() {
			var id = document.getElementById('id').value;
			if ( id == 0 ) {
				alert('Служителят все още не е създаден!');
			} else {
				dialogUpload( id );
			}
		}
		
		function close_form() {
			if( $("nEnableRefresh").value == "1" )
			{
				window.opener.loadXMLDoc('result');
			}
			window.close();
		}
		
		function printContract()
		{
			var sPrintType = document.getElementById( 'sPrintType' ).value;
			var nID = $('id').value;
			
			if( sPrintType && nID )
			{
				switch( sPrintType )
				{
					case 'contract':
						dialogPrintContract( 0, nID );
						break;
					case 'contract_addition':
						dialogPrintContract( 1, nID );
						break;
					case 'order':
						dialogPrintContract( 2, nID );
						break;
					
					default:
						break;
				}
			}
		}

	</script>
{/literal}

{if !$personnel_view}
	<div class="p-3 mb-2 bg-danger text-white"><h1>{$errMsg}</h1></div>
{else}

<dlcalendar click_element_id="img_lk_date" input_element_id="lk_date" tool_tip="Изберете дата"></dlcalendar>

<form name="form1" id="form1" class="ui-nomenclature-dialog ui-contract-dialog ui-person-info" onsubmit="return false;">
	<input type="hidden" id="id" name="id" value="{$id|default:0}" />
	<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />

	{include file='person_tabs.tpl'}

	<div class="container-fluid mb-4">

		<div class="row clearfix mt-2">
			<div class="col-2 col-sm-2 col-lg-2 pl-3">
				<div class="input-group input-group-sm">
					<picture class="w-100">
						<img src="{$image}" class="rounded mx-auto d-block ui-person-photo" alt="Снимка на служителя">
						<button class="btn btn-sm btn-info btn-block" type="button" onclick="return update_image();"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span> Промени</button>
					</picture>
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-name" title="Име..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="fname" type="text" id="fname" placeholder="Име..." />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-name" title="Презиме..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="mname" type="text" id="mname" placeholder="Презиме..." />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-name" title="Фамилия..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="lname" type="text" id="lname" placeholder="Фамилия..." />
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-barcode" title="ЕГН..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="egn" type="text" id="egn" maxlength="10" onkeypress="return formatNumber(event);" placeholder="ЕГН" title="ЕГН"/>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-location" title="Град" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="addr_city" type="text" id="addr_city" placeholder="Град" title="град"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-location" title="Улица" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="addr_street" type="text" id="addr_street" placeholder="Улица" title="Улица"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-location" title="Адрес" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="addr_num" type="text" id="addr_num" placeholder="№" title="№" />
					<input class="form-control" name="addr_floor" type="text" id="addr_floor" placeholder="ет." title="ет." onkeypress="return formatNumber(event);" />
					<input class="form-control" name="addr_app" type="text" id="addr_app" placeholder="ап." title="ап." onkeypress="return formatNumber(event);" />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-phone" title="Телефон..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="home_phone" id="home_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Телефон..." /></td>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-phone" title="Служебен..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="business_phone" id="business_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Служебен..." /></td>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-mobile" title="Мобилен..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="mobile_phone" id="mobile_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Мобилен..." /></td>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-phone" title="Други..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="mphones" id="mphones" type="text" title="Телефонни номера, започващи с префикс 088 и разделени със запетая!" placeholder="08ххх...,08ххх..." />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-2 col-sm-2 col-lg-2 pl-3">
				<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Служебни данни </div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-barcode" title="Код" aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="EIC" id="EIC" onkeypress="return formatDigits(event);" placeholder="КОД"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-barcode" title="Служебна карта" aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="skn" id="skn" onkeypress="return formatDigits(event);" placeholder="СК №&nbsp;"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-mail" title="Email" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="email" id="email" type="text" onkeypress="return formatDigits(event);" placeholder="Еmail"/>
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Други </div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-users" title="Семейно положение..." aria-hidden="true"></span>
					</div>
					<select class="form-control" name="family_status" id="family_status">
						<option value="none"	>неопределено</option>
						<option value="married"	>семеен</option>
						<option value="single"	>несемеен</option>
						<option value="divorce"	>разведен</option>
					</select>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-card" title="IBAN..." aria-hidden="true"></span>
					</div>
					<input class="form-control" name="iban" type="text" id="iban" title="Перонална банкова сметка" placeholder="IBAN"/>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Лична карта </div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-id-card" title="ЛК номер" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="lkn" type="text" id="lkn" maxlength="15" onkeypress="return formatNumber(event);" placeholder="ЛК номер" />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-calendar" title="Дата на издаване" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="lk_date" type="text" id="lk_date" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;<button type="button" id="img_lk_date" class="ui-inline-calendar-trigger" title="Изберете дата" aria-label="Дата на издаване"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-location" title="Място на издаване" aria-hidden="true"></span>
					</div>
					<input class="form-control" name="lk_pub" type="text" id="lk_pub" />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Допълнителна информация</div>
				<div class="input-group input-group-sm mb-1">
					<textarea class="w-100" name="note" rows="5" id="note"></textarea>
				</div>
			</div>
		</div>
	</div>
	<nav class="navbar fixed-bottom flex-row p-2 navbar-expand-lg" id="search">
		<div class="col p-2">
			<div class="input-group input-group-sm">
				<div class="input-group-prepend">
					<span class="ui-icon ui-icon-contract" title="Трудов договор..." aria-hidden="true"></span>
				</div>
				<select class="form-control" name="sPrintType" id="sPrintType">
					<option value="contract">Трудов Договор</option>
					<option value="contract_addition">Доп. споразумение към Трудов Договор</option>
					<option value="order">Прекрат. на трудово правоотношение</option>
				</select>
				<button type="button" class="btn btn-sm btn-info" onclick="printContract();" title="Печат в PDF"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span></button>&nbsp;
			</div>
		</div>
		<div class="col text-right p-2">
			<button type="button" class="btn btn-sm btn-success mr-1" onClick="return submit_form();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
			<button type="button" class="btn btn-sm btn-danger" onClick="close_form();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
		</div>
	</nav>
</form>

{/if}

<script>
	loadXMLDoc('result');
	{if !$personnel_edit}
		
		if( form=document.getElementById('form1') )  
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
	{/if}	
</script>
