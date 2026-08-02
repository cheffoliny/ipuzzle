<script>
{literal}
	rpc_debug = true;
	rpc_html_debug = true;
	
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
<div class="w-100 px-0 mx-0 mb-2 bg-light ui-setup-ppp">
    <dlcalendar click_element_id="sFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
    <dlcalendar click_element_id="sToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>
<form action="" name="form1" id="form1" class="ui-setup-ppp-report" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">

    {include file="tabs_setup_ppp.tpl"}

	<div id="filter" class="container-fluid pt-2 pb-2 ui-setup-ppp-filter">
		<div class="row">
			<div class="col-12 col-md-4">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-upload" aria-hidden="true" title="Тип Предаващ"></span>
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
			<div class="col-12 col-md-4">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-down" aria-hidden="true" title="Тип Получаващ"></span>
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
			<div class="col-12 col-md-4">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-info" aria-hidden="true" title="Статус"></span>
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
			<div class="col-12 col-md-4">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-upload" aria-hidden="true" title="Предаващ"></span>
					</div>
					<input type="text" name="sSourceName" id="sSourceName" class="form-control suggest" suggest="suggest" queryType="pppSourceName" queryParams="sSendType" />
				</div>
			</div>
			<div class="col-12 col-md-4">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-down" aria-hidden="true" title="Получаващ"></span>
					</div>
					<input type="text" name="sDestName" id="sDestName" class="form-control bg-aqua-active" suggest="suggest" queryType="pppDestName" queryParams="sReceiveType" />
				</div>
			</div>
			<div class="col-12 col-md-4">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-barcode" aria-hidden="true" title="Номер на стокова разписка"></span>
					</div>
					<input type="text" name="nNumber" id="nNumber" class="form-control" onkeypress="return formatDigits(event);" />
				</div>
			</div>
		</div>
        <div class="row py-1">
            <div class="col-12 col-lg-4">
                <div class="input-group input-group-sm ui-calendar-input-period" title="Период на стартиране на обекта">
                    <div class="ui-calendar-input-field">
                        <span class="ui-calendar-input-icon"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></span>
                        <input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
                    </div>
                    <span class="ui-calendar-input-separator" aria-hidden="true"><span class="ui-icon ui-icon-exchange"></span></span>
                    <div class="ui-calendar-input-field">
                        <span class="ui-calendar-input-icon"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></span>
                        <input type="text" name="sToDate" id="sToDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
                    </div>
                </div>
            </div>
            <div class="d-none d-lg-block col-lg-4">

            </div>
            <div class="col-12 col-lg-4 text-right ui-setup-ppp-actions">
                <div class="input-group input-group-sm text-right">
                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="openPPP( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
                    <button type="submit" name="Button" class="btn btn-sm btn-primary" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси &nbsp;</button>
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
