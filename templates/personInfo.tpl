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

	<div class="page_caption">{if $id}Редактиране на служител{else}Нов Служител{/if} {$person_name} </div>

	{include file='object_tabs.tpl'}

	<div class="container-fluid mb-4">

		<div class="row clearfix mt-2">
			<div class="col-2 col-sm-2 col-lg-2 pl-3">
				<div class="input-group input-group-sm">
					<picture>
						<img src="{$image}" class="rounded mx-auto d-block" alt="...">
						<button class="btn btn-sm btn-info" type="button" onclick="return update_image();"><i class="far fa-refresh"></i> Промени</button>
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
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-signature fa-fw" data-fa-transform="right-22 down-10" title="Фамилия..."></span>
					</div>
					<input class="form-control" name="lname" type="text" id="lname" placeholder="Фамилия..." />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-eye fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
					</div>
					<select class="form-control" name="statuses" id="statuses" ></select>
				</div>
			</div>
			<div class="col-2 col-sm-2 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-eye fa-fw" data-fa-transform="right-22 down-10" title="Състояние"></span>
					</div>
					<select class="form-control" name="statuses" id="statuses" ></select>
				</div>
			</div>
			<div class="col-2 col-sm-2 col-lg-2">
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
					<input class="form-control" name="mobile_phone" id="mobile_phone" type="text" onkeypress="return formatDigits(event);" placeholder="Мобилен
					\..." /></td>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-phone-plus fa-fw" data-fa-transform="right-22 down-10" title="Други..."></span>
					</div>
					<input class="form-control" name="mphones" id="mphones" type="text" title="Телефонни номера, започващи с префикс 088 и разделени със запетая!" placeholder="08ххх...,08ххх..." />
				</div>
			</div>
		</div>

		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr class="odd">
				<td>
				<!-- начало на работната част -->
		  		    <table class="input">
		  		    	<tr style="height: 20px;">
		  		    		<td colspan="8">&nbsp;</td>
		  		    	</tr>
		                <tr class="even">
		                    <td>Код</td><td align="left"><input name="EIC" type="text" class="inp100" id="EIC" onkeypress="return formatDigits(event);" /></td>
		                    <td colspan="4">&nbsp;</td>
		                    <td colspan="2" rowspan="6" valign="top">
		                    </td>
		                </tr>
		                 <tr class="even">
							<td>ЕГН</td><td><input name="egn" type="text" class="inp100" id="egn" maxlength="10" onkeypress="return formatNumber(event);" /></td>
							<td>град</td>
							<td colspan="2">
								<input name="addr_city" type="text" id="addr_city" style="width: 73px;" />
								&nbsp;ул.<input name="addr_street" type="text" id="addr_street" style="width: 160px;" />
							</td><td>
								&nbsp;№<input name="addr_num" type="text" id="addr_num" style="width: 30px; text-align: right;" />
								&nbsp;ет.<input name="addr_floor" type="text" id="addr_floor" style="width: 28px; text-align: right;" onkeypress="return formatNumber(event);" />
								&nbsp;ап.<input name="addr_app" type="text" id="addr_app" style="width: 28px; text-align: right;" onkeypress="return formatNumber(event);" />
							</td>
		                 </tr>
		                 <tr class="odd">
		                    <td>семейство</td>
		                    <td>
		                        <select class="select150" name="family_status" id="family_status">
		                          	<option value="none">неопределено</option>
		                          	<option value="married">семеен</option>
		                          	<option value="single">несемеен</option>
		                          	<option value="divorce">разведен</option>
		                        </select>
		                    </td>
			                <td>IBAN</td><td><input name="iban" type="text" id="iban" class="inp200" title="Перонална банкова сметка"/></td>
		                    <td align="right">email &nbsp;</td><td><input name="email" id="email" type="text" class="inp150" /></td>
		                 </tr>
		                 <tr class="even">
							<td nowrap>ЛК №</td><td><input name="lkn" type="text" id="lkn" class="inp100" maxlength="15" onkeypress="return formatNumber(event);"  /></td>
							<td>изд. на</td>
							<td colspan="2">
								<input name="lk_date" type="text" id="lk_date" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_lk_date" />
								&nbsp;&nbsp;&nbsp;от&nbsp;<input name="lk_pub" type="text" id="lk_pub" style="width: 131px;" />
							</td>
							<td>СК №&nbsp;<input name="skn" type="text" id="skn" style="width: 117px;" maxlength="15" onkeypress="return formatNumber(event);" /></td>
		                 </tr>
		                 <tr class="odd">
		                    <td colspan=6 valign="top">
								<fieldset style="width: 768px;">
									<legend>Телефони</legend>
									<table class="input">
										<tr>
				</td>
					                    </tr>
									</table>
								</fieldset>
		                    </td>
		                 </tr>
		                 <tr class="even">
		                    <td colspan="8" valign="top">
								<fieldset style="width: 768px;">
									<legend>Допълнителна информация</legend>
									<table class="input">
										<tr>
											<td align="center">
												<textarea name="note" style="width: 748px;" rows="8" id="note"></textarea>
											</td>
					                    </tr>
					                    <tr style="height: 5px;"><td></td></tr>
									</table>
								</fieldset>
		                    </td>
		                 </tr>
		                 <tr class="odd" style="height: 15px;"><td colspan="8"></td></tr>
					</table>
					<table  class="input">
						<tr valign="top" class="odd">
							<td valign="top" align="left" width="310px">
								<select name="sPrintType" id="sPrintType" class="select300">
									<option value="contract">Трудов Договор</option>
									<option value="contract_addition">Доп. споразумение към Трудов Договор</option>
									<option value="order">Прекрат. на трудово правоотношение</option>
								</select>
							</td>
							
							<td valign="top" align="left" width="390px">
								<button class="btn btn-xs btn-info" onclick="printContract();">
                                    <i class="fa fa-file-pdf-o"></i> Печат</button>&nbsp;
							</td>
							
							<td valign="top" align="right" width="100px">
								<button class="search" onclick="return submit_form();"><img src="images/confirm.gif"/>Потвърди</button>&nbsp;
							</td>
							<td valign="top" align="right" width="100px">
								<button id="b100" onClick="close_form();"><img src="images/cancel.gif" />Затвори</button>
							</td>
							
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>

</form>

{/if}

<script>
	loadXMLDoc('result');
	{if !$personnel_edit}
		
		if( form=document.getElementById('form1') )  
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
	{/if}	
</script>
