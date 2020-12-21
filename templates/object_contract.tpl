{literal}
<script>
	rpc_debug = true;
	
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

<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="nIDContract" name="nIDContract" value="0" />

	{include file='object_tabs.tpl'}

	<div class="container-fluid mb-4">
		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-hashtag fa-fw" data-fa-transform="right-22 down-10" title="ID на клиент..."></span>
					</div>
					<input class="form-control" type="text" name="kl_num" id="kl_num" placeholder="ID на клиент..." disabled />
				</div>
			</div>

			<div class="col-9 col-sm-9 col-lg-9 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-info fa-fw" data-fa-transform="right-22 down-10" title="Име на клиент..."></span>
					</div>
					<input class="form-control" type="text"  name="kl_name" id="kl_name" placeholder="Име на клиент..." disabled />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="ЕИН на клиент..."></span>
					</div>
					<input class="form-control" type="text" name="kl_ein" id="kl_ein" placeholder="ЕИН на клиент..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="ЕИН ДДС на клиент..."></span>
					</div>
					<input class="form-control" type="text" name="kl_eindds" id="kl_eindds" placeholder="ЕИН ДДС на клиент..." disabled />
				</div>
			</div>
			<div class="col-6 col-sm-6 col-lg-6 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-map-marker fa-fw" data-fa-transform="right-22 down-10" title="Адресна регистрация..."></span>
					</div>
					<input class="form-control" type="text" name="kl_addr" id="kl_addr" placeholder="Адресна регистрация..." disabled />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-cogs fa-fw" data-fa-transform="right-22 down-10" title="Собственост на техниката..."></span>
					</div>
					<input class="form-control" type="text" name="tech_own" id="tech_own" placeholder="Собственост на техниката..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-cogs fa-fw" data-fa-transform="right-22 down-10" title="Техника по договор..."></span>
					</div>
					<input class="form-control" type="text" name="detectors" id="detectors" placeholder="Техника по договор..." disabled />
				</div>
			</div>

			<div class="col-6 col-sm-6 col-lg-6 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-user fa-fw" data-fa-transform="right-22 down-10" title="МОЛ..."></span>
					</div>
					<input class="form-control" type="text" name="kl_mol" id="kl_mol" placeholder="МОЛ..." disabled />
				</div>
			</div>
		</div>

		<div class="row clearfix mt-2">
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-certificate fa-fw" data-fa-transform="right-22 down-10" title="Номер на договор..."></span>
					</div>
					<input class="form-control" type="text" name="contract_num" id="contract_num" placeholder="Номер на договор..." readonly />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Начин на плащане..."></span>
					</div>
					<input class="form-control" type="text" name="kl_pay" id="kl_pay" placeholder="Начин на плащане..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Отговорност по договор..."></span>
					</div>
					<input class="form-control" type="text" name="tech_single_responsibility" id="tech_single_responsibility"  placeholder="Еднократна..." disabled />
				</div>
			</div>
			<div class="col-3 col-sm-3 col-lg-3 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-credit-card fa-fw" data-fa-transform="right-22 down-10" title="Собственост на техниката..."></span>
					</div>
					<input class="form-control" type="text" name="tech_yearly_responsibility" id="tech_yearly_responsibility" placeholder="Годишна..." disabled />
				</div>
			</div>
		</div>


				
			<div class="col-sm-6 col-md-2" style="width:320px;">

				
				<div class="input-group" style="margin: 2px;">	
					<span class="input-group-addon-ok"><img src="images/glyphicons/start.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="contract_date" id="contract_date" class="clear" readonly />
				</div>
				
				<div class="input-group" style="margin: 2px;">	
					<span class="input-group-addon-ok"><img src="images/glyphicons/stop.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="contract_to" id="contract_to" class="clear" readonly />
				</div>
				
				<div class="input-group" style="margin: 2px;">	
					<span class="input-group-addon-ok"><img src="images/glyphicons/rs.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="contract_rs" id="contract_rs" style="width: 230px; font-weight: bold;" class="clear" readonly />
				</div>
				
				<div class="input-group" style="margin: 2px;">
					<span class="input-group-addon-ok"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
					<textarea id="schet_info" name="schet_info" style="width: 355px; height: 40px;" class="clear" readonly ></textarea>
				</div>
				
				<div class="input-group" style="margin: 2px;">
					<span class="input-group-addon-ok"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
					<textarea id="tech_info" name="tech_info" style="width: 355px; height: 40px;" class="clear" readonly ></textarea>
				</div>
				
				<div class="input-group" style="margin: 2px;">
					<span class="input-group-addon-ok"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
					<textarea id="tech_info" name="tech_info" style="width: 355px; height: 40px;" class="clear" readonly ></textarea>
				</div>
				
				<div class="input-group" style="margin: 2px;">
					<span class="input-group-addon-ok"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="tech_plan" id="tech_plan" class="clear" readonly />
				</div>
			</div>
					
		</div>	


	<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="overflow-x: auto; overflow-y: auto; !important"></div>

	<nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg" id="search">
		<div class="col-6 col-sm-8 col-lg-8 pl-0">
			<div class="input-group input-group-sm">

			</div>
		</div>
		<div class="col-6 col-sm-4 col-lg-4">
			<div class="input-group input-group-sm ml-1">
				<button class="btn btn-sm btn-success mr-1" id="butShift" onClick="formSave();"><i class="fas fa-check" ></i> Запиши </button>
				<button class="btn btn-sm btn-danger" id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><i class="far fa-window-close" ></i> Затвори </button>
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
