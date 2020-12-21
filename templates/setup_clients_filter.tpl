{literal}
	<script>
		
		rpc_debug = true;
		
		function onInit()
		{
			rpc_on_exit = function()
			{
				onAutoChange();
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( 'load' );
		}
		
		function formSubmit()
		{
			loadXMLDoc2( 'save', 5 );
		}
		
		function onAutoChange()
		{
			if( $("nAuto").checked == true )
			{
				$("sFromDate").disabled = "";
				$("sPeriod").disabled = "";
				document.getElementById( "editFromDate" ).disabled = "";
			}
			else
			{
				$("sFromDate").disabled = "disabled";
				$("sPeriod").disabled = "disabled";
				document.getElementById( "editFromDate" ).disabled = "disabled";
			}
		}
	</script>
{/literal}

<dlcalendar click_element_id="editFromDate" input_element_id="sFromDate" tool_tip="Изберете дата"></dlcalendar>

<form id="form1" action="" onsubmit="return false;">
    <div class="modal-content pb-3">
        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID} Редакция на{else} Добавяне на{/if} филтър</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-5">

            <input type="hidden" name="nID" id="nID" value="{$nID}">

            <div class="row mb-1">
                <div class="col-6 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fas fa-hashtag fa-fw" data-fa-transform="right-22 down-10" title="Име на филтър..."></span>
                        </div>
                        <input class="form-control" id="filter_name" name="filter_name" type="text"  placeholder="Име на филтър..." />
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="is_default" name="is_default" />
                        <label class="custom-control-label" for="is_default">Направи основен</label>
                    </div>
                </div>
            </div>

            <nav id="navbar-example" class="navbar navbar-light bg-secondary text-white my-4">
                Колони за визуализация
            </nav>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="name" id="name" />
                        <label class="custom-control-label" for="name">Име</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="invoice_ein" id="invoice_ein" />
                        <label class="custom-control-label" for="invoice_ein">ЕИН / ЕГН</label>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="invoice_mol" id="invoice_mol" />
                        <label class="custom-control-label" for="invoice_mol">МОЛ</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="invoice_address" id="invoice_address" />
                        <label class="custom-control-label" for="invoice_address">Адрес по регистрация</label>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="email" id="email" />
                        <label class="custom-control-label" for="email">E-mail</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="address" id="address" />
                        <label class="custom-control-label" for="address">Адресна за кореспонденция</label>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="phone" id="phone" />
                        <label class="custom-control-label" for="phone">Телефон</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="invoice_bring_to_object" id="invoice_bring_to_object" />
                        <label class="custom-control-label" for="invoice_bring_to_object">Фактура на място</label>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="invoice_payment" id="invoice_payment" />
                        <label class="custom-control-label" for="invoice_payment">Начин на плащане</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="invoice_layout" id="invoice_layout" />
                        <label class="custom-control-label" for="invoice_layout">Формат на печат</label>
                    </div>
                </div>
            </div>

            <div class="row mb-5 pb-5">
                <div class="col-6">
                    &nbsp;
                </div>
                <div class="col-6">
                    &nbsp;
                </div>
            </div>

            {*<input type="checkbox" class="clear" name="object_name" id="object_name">*}
            {*Име на Обект*}
            {*<input type="checkbox" class="clear" name="object_num" id="object_num">*}
            {*Номер на Обект*}
            {*<input type="checkbox" class="clear" name="object_city" id="object_city">*}
            {*Населено Място*}

            {*<td align="right">Носене Фактура до Адрес:&nbsp;</td>*}
            {*<input type="checkbox" class="clear" name="nBringInvoice" id="nBringInvoice">*}
            {*<td align="right">Начин на Плащане:&nbsp;</td>*}
            {*<select type="text" class="select200" name="sPayment" id="sPayment">*}
                {*<option value="">-- Избери --</option>*}
                {*<option value="bank">Фактура по банка</option>*}
                {*<option value="cash">Фактура в брой</option>*}
                {*<option value="receipt">Квитанция</option>*}
            {*</select>*}
            {*<td align="right">Изглед на Фактурата:&nbsp;</td>*}
            {*<select type="text" class="select200" name="sLayout" id="sLayout">*}
            {*<option value="">-- Избери --</option>*}
                {*<option value="single">Единичен</option>*}
                {*<option value="by_services">По услуги</option>*}
                {*<option value="by_objects">По Обекти</option>*}
                {*<option value="detail">Подробен</option>*}
            {*</select>*}

            {*<select type="text" class="select150" name="sIsPaid" id="sIsPaid">*}
                {*<option value="">Платил / Не платил</option>*}
                {*<option value="paid">Платил</option>*}
                {*<option value="unpaid">Не платил</option>*}
            {*</select>*}
	         {*за текущия месец*}
			{*<input type="checkbox" class="clear" name="nSalesDocsToPay" id="nSalesDocsToPay">*}
            {*С не погасен документ за продажба*}
            {*<input type="checkbox" class="clear" name="nSingleToPay" id="nSingleToPay">*}
			{*С не погасени еднократни задължения*}
            {*<td align="right">Име на Обект:&nbsp;</td>*}
            {*<input type="text" class="inp200" name="sObjectName" id="sObjectName">*}
            {*<td align="right">Номер на Обект:&nbsp;</td>*}
            {*<input type="text" class="inp200" name="sObjectNum" id="sObjectNum">*}
            {*<td align="right">Населено Място:&nbsp;</td>*}
            {*<input type="text" class="inp200" name="sObjectCity" id="sObjectCity">*}
        </div>

        <nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="input-group input-group-sm text-right">
                    <button class="btn btn-block btn-sm btn-primary" onClick="formSubmit();"><i class="fa fa-plus"></i> Добави</button>
                </div>
            </div>
        </nav>

</form>

<script>
	onInit();
</script>