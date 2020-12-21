{literal}
	<script>
		rpc_debug = true;
		rpc_method='POST';
			
		function onInit()
		{
			loadXMLDoc2( 'load' );
		}
		
		function formSubmit()
		{
			loadXMLDoc2( 'save', 3 );
		}
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">

    {include file='client_tabs.tpl'}

    <div class="container-fluid mb-4">
        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fab fa-slack fa-fw" data-fa-transform="right-22 down-10" title="Клиентски номер..."></span>
                    </div>
                    <input class="form-control form-control" type="text" id="nID" name="nID" value="{$nID|default:0}" placeholder="Клиентски номер..." readonly />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-user fa-fw" data-fa-transform="right-22 down-10" title="Име на клиент..."></span>
                    </div>
                    <input class="form-control" type="text" id="sName" name="sName" placeholder="Име на клиент..." />
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-at fa-fw" data-fa-transform="right-22 down-10" title="E-mail..."></span>
                    </div>
                    <input class="form-control form-control" type="email" id="sEmail" name="sEmail" placeholder="E-mail..." disabled />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-envelope-square fa-fw" data-fa-transform="right-22 down-10" title="Адрес за кореспонденция..."></span>
                    </div>
                    <input class="form-control" type="text" id="sAddress" name="sAddress" placeholder="Адрес за кореспонденция..." />
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fab fa-viber fa-fw" data-fa-transform="right-22 down-10" title="Телефон..."></span>
                    </div>
                    <input class="form-control form-control" type="phone" id="sPhone" name="sPhone" placeholder="Телефон..." disabled />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="far fa-comment-alt fa-fw" data-fa-transform="right-22 down-7" title="Допълнителна информация..."></span>
                    </div>
                    <textarea class="form-control py-0" row="2" id="sNote" name="sNote" placeholder="Допълнителна информация..." ></textarea>
                </div>
            </div>
        </div>

        <nav id="navbar-example" class="navbar navbar-light bg-primary text-white my-3">
            <p>Информация за фактура</p>
        </nav>

        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="ЕИН..."></span>
                    </div>
                    <input class="form-control form-control" type="email" id="sInvoiceEIN" name="sInvoiceEIN" placeholder="ЕИН..." disabled />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-map-marker fa-fw" data-fa-transform="right-22 down-10" title="Адрес за фактура..."></span>
                    </div>
                    <input class="form-control" type="phone" id="sInvoiceAddress" name="sInvoiceAddress" placeholder="Адрес за фактура..." />
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="ЕИН ДДС..."></span>
                    </div>
                    <input class="form-control form-control" type="text" id="sInvoiceEINDDS" name="sInvoiceEINDDS" placeholder="ЕИН ДДС..." disabled />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-user-circle fa-fw" data-fa-transform="right-22 down-10" title="МОЛ..."></span>
                    </div>
                    <input class="form-control" type="text" id="sInvoiceMOL" name="sInvoiceMOL" placeholder="МОЛ..." />
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    &nbsp;
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-user-circle fa-fw" data-fa-transform="right-22 down-10" title="МОЛ..."></span>
                    </div>
                    <input class="form-control" type="text" id="sInvoiceRecipient" name="sInvoiceRecipient" placeholder="Получател..." />
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3">
                <div class="custom-control custom-checkbox ">
                    <input class="custom-control-input" type="checkbox" id="nInvoiceBringToObject" name="nInvoiceBringToObject" />
                    <label class="custom-control-label text-white" for="nInvoiceBringToObject">Фактура на място</label>
                </div>
            </div>
            <div class="col-4 col-sm-4 col-lg-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-envelope-square fa-fw" data-fa-transform="right-22 down-10" title="Изглед на фактура..."></span>
                    </div>
                    <select class="form-control" id="sInvoiceLayout" name="sInvoiceLayout">
                        <option value="single">Едноредов печат</option>
                        <option value="by_services">Печат по услуги</option>
                        <option value="by_objects">Печат по обекти</option>
                        <option selected value="total">Подробен печат</option>
                    </select>
                </div>
            </div>
            <div class="col-4 col-sm-4 col-lg-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-envelope-square fa-fw" data-fa-transform="right-22 down-10" title="Предпочитание за плащане..."></span>
                    </div>
                    <select class="form-control" id="sInvoicePayment" name="sInvoicePayment">
                        <option value="bank">Фактура по банка</option>
                        <option value="cash">Фактура в брой</option>
                        <option value="receipt">Проформа</option>
                    </select>
                </div>
            </div>
        </div>

        {*<div class="row clearfix mt-2">*}
            {*<div class="col-3 col-sm-3 col-lg-3">*}
                {*<div class="custom-control custom-checkbox" id="bankEnableEmailData" >*}
                    {*<input class="custom-control-input" type="checkbox"id="nSendByEmail" name="nSendByEmail" />*}
                    {*<label class="custom-control-label text-white" for="nInvoiceBringToObject">Фактура на e-mail</label>*}
                {*</div>*}
            {*</div>*}
            {*<div class="col-4 col-sm-4 col-lg-4">*}
                {*<div class="input-group input-group-sm" id="bankInvoiceEmailData">*}
                    {*<div class="input-group-prepend">*}
                        {*<span class="fas fa-envelope-square fa-fw" data-fa-transform="right-22 down-10" title="Изглед на фактура..."></span>*}
                    {*</div>*}
                    {*<input class="form-control" type="email" id="sInvoiceEmail" name="sInvoiceEmail" />*}
                {*</div>*}
            {*</div>*}
            {*<div class="col-4 col-sm-4 col-lg-4">*}
                {*<div class="input-group input-group-sm">*}
                    {*&nbsp;*}
                {*</div>*}
            {*</div>*}
        {*</div>*}

    </div>

    <nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg mb-1" id="search">
        <div class="col-6 col-sm-8 col-lg-8 pl-0">
            <div class="input-group input-group-sm">
                &nbsp;
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-4">
            <div class="input-group input-group-sm ml-1">
                <button class="btn btn-sm btn-success mr-1"	onClick="formSubmit();"         ><i class="fas fa-check" ></i> Запиши </button>
                <button class="btn btn-sm btn-danger"	    onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
            </div>
        </div>
    </nav>

</form>
</div>

<script>
	onInit();
</script>