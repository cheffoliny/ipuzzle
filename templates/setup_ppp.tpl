<script>
{literal}
	rpc_debug = true;
	
	function openPPP( id )
	{
		var params = 'id=' + id;
		dialogPPP2( params );
	}
	
	function nullSentType()
	{
		document.getElementById('sSourceName').value = '';
	}
	
	function nullReceivedType()
	{
		document.getElementById('sDestName').value = '';
	}

{/literal}
</script>
<div class="w-100 px-0 mx-0 mb-2 bg-light">
    <dlcalendar click_element_id="sFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
    <dlcalendar click_element_id="sToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">

    {include file="tabs_setup_ppp.tpl"}

	<div id="filter" class="container pt-2 pb-2">
		<div class="row">
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-upload fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<select class="form-control" name="sSendType" id="sSendType" onchange="nullSentType();">
						<option value="">-- Тип Предаващ --</option>
						<option value="object">Обект</option>
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="client">Доставчик</option>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-download fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<select name="sReceiveType" id="sReceiveType" class="form-control" onchange="nullReceivedType();">
						<option value="">-- Тип Получаващ --</option>
						<option value="object">Обект</option>
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="client">Доставчик</option>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="far fa-exclamation fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></i>
					</div>
					<select name="sStatus" id="sStatus" class="form-control">
						<option value="">-- Всички статуси --</option>
						<option value="confirm">Потвърдени</option>
						<option value="open">Непотвърдени</option>
						<option value="cancel">Анулирани</option>
					</select>
				</div>
			</div>
		</div>
		<div class="row py-1">
			<div class="col">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="fas fa-upload fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<input type="text" name="sSourceName" id="sSourceName" class="form-control suggest" suggest="suggest" queryType="pppSourceName" queryParams="sSendType" />
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="fas fa-download fa-fw" data-fa-transform="right-22 down-10" title="Тип Приемащ..."></span>
					</div>
					<input type="text" name="sDestName" id="sDestName" class="form-control bg-aqua-active" suggest="suggest" queryType="pppDestName" queryParams="sReceiveType" />
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="fas fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Номер на стокова разписка...."></span>
					</div>
					<input type="text" name="nNumber" id="nNumber" class="form-control" onkeypress="return formatDigits(event);" />
				</div>
			</div>
		</div>
        <div class="row py-1">
            <div class="col">
                <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                    <div class="input-group-prepend">
                        <span id="editFromDate" id="editToDate" class="fas fa-calendar-alt fa-fw" data-fa-transform="right-22 down-10" ></span>
                    </div>
                    <input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
                    <input type="text" name="sToDate" id="sToDate" class="form-control input-group-addon" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
                    {*<div class="input-group-append-r">*}
                    {*<span id="editToDate" class="fa fa-calendar-plus-o" title="Край на периода"></span>*}
                    {*</div>*}
                </div>
            </div>
            <div class="col">

            </div>
            <div class="col text-right">
                <div class="input-group input-group-sm text-right">
                    <button class="btn btn-sm btn-success mr-2" onclick="openPPP( 0 );"><i class="fa fa-plus fa-lg"></i> Добави </button>
                    <button type="submit" name="Button" class="btn btn-sm btn-primary" onclick="loadXMLDoc2( 'result' );"><i class="fa fa-search fa-lg"></i> Търси &nbsp;</button>
                </div>
            </div>
        </div>
	</div>

	<div id="result"></div>

</form>
</div>
<script>
	loadXMLDoc2( 'setDefaults' );
</script>