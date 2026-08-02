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

<form name="form1" id="form1" class="ui-client-core ui-client-info" onsubmit="return false;">

    {include file='client_tabs.tpl'}

    <div class="container-fluid mb-4 ui-client-info-fields">
        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-code" aria-hidden="true" title="Клиентски номер..."></span>
                    </div>
                    <input class="form-control form-control" type="text" id="nID" name="nID" value="{$nID|default:0}" placeholder="Клиентски номер..." readonly />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-user" aria-hidden="true" title="Име на клиент..."></span>
                    </div>
                    <input class="form-control" type="text" id="sName" name="sName" placeholder="Име на клиент..." />
                </div>
            </div>
        </div>

        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-at" aria-hidden="true" title="E-mail..."></span>
                    </div>
                    <input class="form-control form-control" type="email" id="sEmail" name="sEmail" placeholder="E-mail..." />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-mail" aria-hidden="true" title="Адрес за кореспонденция..."></span>
                    </div>
                    <input class="form-control" type="text" id="sAddress" name="sAddress" placeholder="Адрес за кореспонденция..." />
                </div>
            </div>
        </div>

        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-phone" aria-hidden="true" title="Телефон..."></span>
                    </div>
                    <input class="form-control form-control" type="phone" id="sPhone" name="sPhone" placeholder="Телефон..." />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-comment" aria-hidden="true" title="Допълнителна информация..."></span>
                    </div>
                    <textarea class="form-control py-0" rows="2" id="sNote" name="sNote" placeholder="Допълнителна информация..." ></textarea>
                </div>
            </div>
        </div>

        <nav id="navbar-example" class="navbar navbar-light bg-primary text-white my-3 ui-client-section-title">
            <h6 class="py-2">Информация за фактура</h6>
        </nav>

        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-barcode" aria-hidden="true" title="ЕИН..."></span>
                    </div>
                    <input class="form-control form-control" type="text" id="sInvoiceEIN" name="sInvoiceEIN" placeholder="ЕИН..." />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-location" aria-hidden="true" title="Адрес за фактура..."></span>
                    </div>
                    <input class="form-control" type="text" id="sInvoiceAddress" name="sInvoiceAddress" placeholder="Адрес за фактура..." />
                </div>
            </div>
        </div>

        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-barcode" aria-hidden="true" title="ЕИН ДДС..."></span>
                    </div>
                    <input class="form-control form-control" type="text" id="sInvoiceEINDDS" name="sInvoiceEINDDS" placeholder="ЕИН ДДС..." />
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-user" aria-hidden="true" title="МОЛ..."></span>
                    </div>
                    <input class="form-control" type="text" id="sInvoiceMOL" name="sInvoiceMOL" placeholder="МОЛ..." />
                </div>
            </div>
        </div>

        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    &nbsp;
                </div>
            </div>
            <div class="col-9 col-sm-9 col-lg-9">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-user" aria-hidden="true" title="Получател..."></span>
                    </div>
                    <input class="form-control" type="text" id="sInvoiceRecipient" name="sInvoiceRecipient" placeholder="Получател..." />
                </div>
            </div>
        </div>

        <div class="row mx-1 my-2">
            <div class="col-3 col-sm-3 col-lg-3">
                <div class="custom-control custom-checkbox ">
                    <input class="custom-control-input" type="checkbox" id="nInvoiceBringToObject" name="nInvoiceBringToObject" />
                    <label class="custom-control-label text-white" for="nInvoiceBringToObject">Фактура на място</label>
                </div>
            </div>
            <div class="col-4 col-sm-4 col-lg-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-layout" aria-hidden="true" title="Изглед на фактура..."></span>
                    </div>
                    <select class="form-control" id="sInvoiceLayout" name="sInvoiceLayout">
                        <option value="single">Едноредов печат</option>
                        <option value="by_services">Изглед услуги</option>
                        <option value="by_objects">Изглед обекти</option>
                        <option value="detail">Изглед месеци</option>
                        <option selected value="extended">Подробен изглед</option>
                    </select>
                </div>
            </div>
            <div class="col-4 col-sm-4 col-lg-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-card" aria-hidden="true" title="Предпочитание за плащане..."></span>
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
                        {*<span class="ui-icon ui-icon-mail" aria-hidden="true" title="Изглед на фактура..."></span>*}
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

    <nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg mb-1 ui-client-actions" id="search">
        <div class="col-6 col-sm-8 col-lg-8 pl-0">
            <div class="input-group input-group-sm">
                &nbsp;
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-4">
            <div class="input-group input-group-sm ml-1">
                <button type="button" class="btn btn-sm btn-success mr-1" onClick="formSubmit();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
                <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
            </div>
        </div>
    </nav>

</form>

<script>
	onInit();
</script>
