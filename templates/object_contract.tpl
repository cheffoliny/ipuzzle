{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;
	
	function openContract(id) {
		window.location.href = 'page.php?page=object_contract&nID=' + id;
	}	


	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}		

	function formSave() {
		loadXMLDoc2('save');
	}
</script>
{/literal}

<form name="form1" id="form1" class="ui-object-core ui-object-contract" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="nIDContract" name="nIDContract" value="0" />

	{include file='object_tabs.tpl'}

	<div class="container-fluid ui-object-contract-summary">
		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-code" title="ID на клиент..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="kl_num" id="kl_num" placeholder="ID на клиент..." disabled />
				</div>
			</div>

			<div class="col-9 col-sm-9 col-lg-9 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-info" title="Име на клиент..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text"  name="kl_name" id="kl_name" placeholder="Име на клиент..." disabled />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-barcode" title="ЕИН на клиент..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="kl_ein" id="kl_ein" placeholder="ЕИН на клиент..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-barcode" title="ЕИН ДДС на клиент..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="kl_eindds" id="kl_eindds" placeholder="ЕИН ДДС на клиент..." disabled />
				</div>
			</div>
			<div class="col-6 col-sm-6 col-lg-6 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-location" title="Адресна регистрация..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="kl_addr" id="kl_addr" placeholder="Адресна регистрация..." disabled />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-settings" title="Собственост на техниката..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="tech_own" id="tech_own" placeholder="Собственост на техниката..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-settings" title="Техника по договор..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="detectors" id="detectors" placeholder="Техника по договор..." disabled />
				</div>
			</div>

			<div class="col-6 col-sm-6 col-lg-6 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-user" title="МОЛ..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="kl_mol" id="kl_mol" placeholder="МОЛ..." disabled />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-certificate" title="Номер на договор..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="contract_num" id="contract_num" placeholder="Номер на договор..." readonly />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-card" title="Начин на плащане..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="kl_pay" id="kl_pay" placeholder="Начин на плащане..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-contract" title="Отговорност по договор..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="tech_single_responsibility" id="tech_single_responsibility"  placeholder="Еднократна..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 px-1">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-card" title="Собственост на техниката..." aria-hidden="true"></span>
					</div>
					<input class="form-control" type="text" name="tech_yearly_responsibility" id="tech_yearly_responsibility" placeholder="Годишна..." disabled />
				</div>
			</div>
		</div>


				
		<div class="row clearfix mt-2">
			<div class="col-12 px-1">
				<div class="ui-object-contract-details">

				
				<div class="input-group input-group-sm ui-object-contract-detail">
					<span class="input-group-prepend"><span class="ui-icon ui-icon-play" aria-hidden="true"></span></span>
					<input type="text" name="contract_date" id="contract_date" class="form-control clear" readonly />
				</div>
				
				<div class="input-group input-group-sm ui-object-contract-detail">
					<span class="input-group-prepend"><span class="ui-icon ui-icon-stop" aria-hidden="true"></span></span>
					<input type="text" name="contract_to" id="contract_to" class="form-control clear" readonly />
				</div>
				
				<div class="input-group input-group-sm ui-object-contract-detail">
					<span class="input-group-prepend"><span class="ui-icon ui-icon-document" aria-hidden="true"></span></span>
					<input type="text" name="contract_rs" id="contract_rs" class="form-control clear ui-object-contract-number" readonly />
				</div>
				
				<div class="input-group input-group-sm ui-object-contract-detail">
					<span class="input-group-prepend"><span class="ui-icon ui-icon-info" aria-hidden="true"></span></span>
					<input type="text" name="tech_plan" id="tech_plan" class="form-control clear" readonly />
				</div>

				<div class="input-group input-group-sm ui-object-contract-detail ui-object-contract-detail-wide">
					<span class="input-group-prepend"><span class="ui-icon ui-icon-info" aria-hidden="true"></span></span>
					<textarea id="schet_info" name="schet_info" class="form-control clear" rows="2" readonly></textarea>
				</div>
				
				<div class="input-group input-group-sm ui-object-contract-detail ui-object-contract-detail-wide">
					<span class="input-group-prepend"><span class="ui-icon ui-icon-info" aria-hidden="true"></span></span>
					<textarea id="tech_info" name="tech_info" class="form-control clear" rows="2" readonly></textarea>
				</div>
				</div>
			</div>
		</div>
					
	</div>

	<div class="ui-object-contract-result-shell">
		<div id="result" class="ui-object-result ui-contract-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>
	</div>

	<nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg ui-object-actions ui-object-contract-actions" id="search">
		<div class="col-6 col-sm-8 col-lg-8 pl-0">
			<div class="input-group input-group-sm">

			</div>
		</div>
		<div class="col-6 col-sm-4 col-lg-4">
			<div class="input-group input-group-sm ml-1">
				<button type="button" class="btn btn-sm btn-success mr-1" id="butShift" onClick="formSave();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
				<button type="button" class="btn btn-sm btn-danger" id="b100" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
			</div>
		</div>
	</nav>

</form>


<script>
	loadXMLDoc2('result');
	
	{if !$edit.object_contract_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}	
</script>
