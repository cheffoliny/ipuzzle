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

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="id" name="id" value="{$id|default:0}" />
	<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />

	{include file='person_tabs.tpl'}

	<div class="container-fluid mb-4">

		<div class="row clearfix mt-2">
			<div class="col-2 col-sm-2 col-lg-2 pl-3">
				<div class="input-group input-group-sm">
					<picture class="w-100">
						<img src="{$image}" class="rounded mx-auto d-block" alt="...">
						<button class="btn btn-sm btn-info btn-block" type="button" onclick="return update_image();"><i class="far fa-refresh"></i> Промени</button>
					</picture>
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-signature fa-fw" data-fa-transform="right-22 down-10" title="Име..."></span>
					</div>
					<input class="form-control" name="fname" type="text" id="fname" placeholder="Име..." />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-signature fa-fw" data-fa-transform="right-22 down-10" title="Презиме..."></span>
					</div>
					<input class="form-control" name="mname" type="text" id="mname" placeholder="Презиме..." />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-signature fa-fw" data-fa-transform="right-22 down-10" title="Фамилия..."></span>
					</div>
					<input class="form-control" name="lname" type="text" id="lname" placeholder="Фамилия..." />
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="ЕГН..."></span>
					</div>
					<input class="form-control" name="egn" type="text" id="egn" maxlength="10" onkeypress="return formatNumber(event);" placeholder="ЕГН" title="ЕГН"/>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-map-marked-alt fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
					</div>
					<input class="form-control" name="addr_city" type="text" id="addr_city" placeholder="Град" title="град"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-map-marked-alt fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
					</div>
					<input class="form-control" name="addr_street" type="text" id="addr_street" placeholder="Улица" title="Улица"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-map-marked-alt fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
					</div>
					<input class="form-control" name="addr_num" type="text" id="addr_num" placeholder="№" title="№" />
					<input class="form-control" name="addr_floor" type="text" id="addr_floor" placeholder="ет." title="ет." onkeypress="return formatNumber(event);" />
					<input class="form-control" name="addr_app" type="text" id="addr_app" placeholder="ап." title="ап." onkeypress="return formatNumber(event);" />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-phone-alt fa-fw" data-fa-transform="right-22 down-10" title="Телефон..."></span>
					</div>
					<input class="form-control" name="home_phone" id="home_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Телефон..." /></td>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-phone-office fa-fw" data-fa-transform="right-22 down-10" title="Служебен..."></span>
					</div>
					<input class="form-control" name="business_phone" id="business_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Служебен..." /></td>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-mobile-android fa-fw" data-fa-transform="right-22 down-10" title="Мобилен..."></span>
					</div>
					<input class="form-control" name="mobile_phone" id="mobile_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Мобилен..." /></td>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-phone-plus fa-fw" data-fa-transform="right-22 down-10" title="Други..."></span>
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
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Име..."></span>
					</div>
					<input class="form-control" type="text" name="EIC" id="EIC" onkeypress="return formatDigits(event);" placeholder="КОД"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Име..."></span>
					</div>
					<input class="form-control" type="text" name="skn" id="skn" onkeypress="return formatDigits(event);" placeholder="СК №&nbsp;"/>
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-mailbox fa-fw" data-fa-transform="right-22 down-10" title="Име..."></span>
					</div>
					<input class="form-control" name="email" id="email" type="text" onkeypress="return formatDigits(event);" placeholder="Еmail"/>
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Други </div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="far fa-users fa-fw" data-fa-transform="right-22 down-10" title="Семейно положение..."></span>
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
						<span class="far fa-badge-dollar fa-fw" data-fa-transform="right-22 down-10" title="IBAN..."></span>
					</div>
					<input class="form-control" name="iban" type="text" id="iban" title="Перонална банкова сметка" placeholder="IBAN"/>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm mb-1 text-white bg-dark p-2"> Лична карта </div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-passport fa-fw" data-fa-transform="right-22 down-10" title="ЛК номер"></span>
					</div>
					<input class="form-control" name="lkn" type="text" id="lkn" maxlength="15" onkeypress="return formatNumber(event);" placeholder="ЛК номер" />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-map-marked-alt fa-fw" data-fa-transform="right-22 down-10" title="Дата на издаване"></span>
					</div>
					<input class="form-control" name="lk_date" type="text" id="lk_date" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_lk_date" />
				</div>
				<div class="input-group input-group-sm mb-1">
					<div class="input-group-prepend">
						<span class="fa fa-map-marked-alt fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
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
					<span class="fa fa-file fa-fw" data-fa-transform="right-22 down-10" itle="Трудов Договор..."></span>
				</div>
				<select class="form-control" name="sPrintType" id="sPrintType">
					<option value="contract">Трудов Договор</option>
					<option value="contract_addition">Доп. споразумение към Трудов Договор</option>
					<option value="order">Прекрат. на трудово правоотношение</option>
				</select>
				<button class="btn btn-sm btn-info" onclick="printContract();"><i class="far fa-file-pdf-o"></i></button>&nbsp;
			</div>
		</div>
		<div class="col text-right p-2">
			<button class="btn btn-sm btn-success mr-1"	onClick="return submit_form();" ><i class="fas fa-check" ></i> Запиши </button>
			<button class="btn btn-sm btn-danger"	    onClick="close_form();"			><i class="far fa-window-close" ></i> Затвори </button>
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
