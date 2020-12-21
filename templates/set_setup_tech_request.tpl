{literal}
<script>
    rpc_debug = true;

    InitSuggestForm = function() {
        for(var i = 0; i < suggest_elements.length; i++) {
            if( suggest_elements[i]['id'] == 'obj' ) {
                suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
            }

            if( suggest_elements[i]['id'] == 'person' ) {
                suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
            }
        }
    };

    function offerDeal() {
        //Офертите
        var idContract = parseInt(jQuery('#nIDContract').val(), 10),
            nIDAscertainmentProtocol = parseInt(jQuery('#nIDAscertainmentProtocol').val(), 10);

        if(idContract !== 0) { goToStep(4);}
        else if(nIDAscertainmentProtocol) {goToStep(3);}
    }

    function disableInputs () {
        var inputsAll = jQuery('select, input:not(:hidden)').attr('disabled', 'disabled');
    }

    function checkIdRequest() {

        jQuery('#to_plan').hide();

        if(  parent.document.getElementById('id_request') != null)
        {
            var parent_id_request = parent.document.getElementById('id_request').value;
            if (parent_id_request > 0) {
                jQuery('.search').hide();
            }
        }
    }

    function onSuggestObject(aParams) {
        $('nObject').value = aParams.KEY;
        var idObject = aParams.KEY;

        rpc_on_exit = function( nCode )
        {
            if( !parseInt( nCode ) )
            {
//                    setPlanSupport(idObject);

                if( $("sUnprocessed").value != "" )
                {
                    var sMessage = "НЕОБРАБОТЕНИ ЗАДАЧИ:\n \n";

                    sMessage += $("sUnprocessed").value + '\n';
                    sMessage += "ИСКАТЕ ЛИ ГЕНЕРИРАНЕ НА НОВА ЗАЯВКА?";

                    if( !confirm( sMessage ) )
                    {
                        $("nObject").value = 0;
                        $("sLastService").value = "";
                        $("obj").value = "";
                    }
                    else
                    {
                        document.getElementById( 'nIDFirm' ).value = $("nTempIDFirm").value;
                        formChangeByObject();
                    }
                }
                else
                {
                    document.getElementById( 'nIDFirm' ).value = $("nTempIDFirm").value;
                    formChangeByObject();
                }
            }
        };

        loadXMLDoc2( 'getunprocessed' );
    }

    function onSuggestPerson(aParams)
    {
        jQuery('#nIDPerson').val(aParams.KEY);
    }
	
	function formChange() {
		document.getElementById('sAct').value = 'select';
		objChange();
		loadXMLDoc2('load');
	}
	
	function formChangeByObject()
	{
		document.getElementById('sAct').value = 'select';
		
		rpc_on_exit = function( nCode )
		{
			if( !parseInt( nCode ) )
			{
				document.getElementById( 'nIDOffice' ).value = $("nTempIDOffice").value;
			}
			
			rpc_on_exit = function( nCode ) {}
		}
		
		loadXMLDoc2( 'load' );
	}

    function objChange() {

        if(empty($('obj').value)) {
            $('nObject').value = 0;
            $("sLastService").value = "";
            $('obj').value = '';
        }

    }

    function typeChange() {
        loadXMLDoc2('getReasons');
    }
	
	function formSubmit()
	{
		loadXMLDoc2('save', 3);
	}
	
	function formSubmitAndPlanning() {

		loadXMLDoc2('save', 3);

	}
	
	function openObjectServices()
	{
		var nID = $("nObject").value;
		
		if( nID != 0 )
		{
			dialogObjectSupport( '&nID=' + nID );
		}
	}
</script>
{/literal}

<dlcalendar click_element_id="imgPlannedStart" input_element_id="sPlannedStart" tool_tip="Изберете дата"></dlcalendar>

<div class="modal-content pb-5">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">

		<div class="modal-header">
			<h6 class="modal-title text-white">{if $nID}Редакция{else}Добавяне{/if} на задача</h6>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body pb-3">

			<input type="hidden" id="nID" name="nID" value="{$nID}">
			<input type="hidden" id="sAct" name="sAct" value="load" />
			<input type="hidden" id="nObject" name="nObject" value="0" />
			<input type="hidden" id="nNum" name="nNum" value="0" />
			<input type="hidden" id="nOld" name="nOld" value="0" />
			<input type="hidden" id="sUnprocessed" name="sUnprocessed" value="" />
			<input type="hidden" id="bDis" name="bDis" value="{$bDis}" />
			{*Оферта*}
			<input type="hidden" id="nIDContract" name="nIDContract" value="0" />  {* Има ли оферта по тази заявка *}
			<input type="hidden" id="bIsSigned"   name="bIsSigned"   value="0" />  {* Дали офертата е потвърдена *}
			<input type="hidden" id="bIsUpdated"  name="bIsUpdated"  value="0" />  {* Дали полето е ъпдейтвано - ако да, приемаме, че офертата е дефинирана *}

			{*Констативен проткол*}
			{*<input type="hidden" id="nIDAscertainmentProtocol" name="nIDAscertainmentProtocol" value="0" />*}

			<input type="hidden" id="nTempIDFirm" name="nTempIDFirm" value="0" />
			<input type="hidden" id="nTempIDOffice" name="nTempIDOffice" value="0" />

			<input type="hidden" id="nToPlanning" name="nToPlanning" value="0" />
			<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />
			<input type="hidden" id="nIsTemplate" name="nIsTemplate" value="0" />

			<div class="row mb-1">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-tasks fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<input class="form-control" type="text" name="sNum" id="sNum" placeholder="Номер/Дата/..." disabled="disabled" />
					</div>
				</div>
			</div>
			<div class="row mb-1">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-clock fa-fw" data-fa-transform="right-22 down-10" title="Задача" id="imgPlannedStart" title="Планиран старт"></span>
						</div>
						<input type="text" name="sPlannedStartH" id="sPlannedStartH" class="form-control" readonly onkeypress="return formatTime(event);" maxlength="5" title="Планиран старт" placeholder="Планиран час" />
						<input type="text" name="sPlannedStart" id="sPlannedStart" class="form-control" readonly onkeypress="return formatDate(event, '.');" maxlength="10" title="Планиран старт" placeholder="Планиран старт" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-wrench fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<input title="Работна карта" placeholder="Работна карта" type="text" name="nIDLimitCard" id="nIDLimitCard" class="form-control" readonly />
					</div>
				</div>
			</div>
		</div>

		<div class="modal-body pb-3">
			<div class="row">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fa fa-home fa-fw" data-fa-transform="right-22 down-10" title="Обект..."></span>
						</div>
						<input class="form-control suggest" type="text" name="obj" id="obj" suggest="suggest" queryType="obj" queryParams="nIDOffice" onChange="objChange();" />
						<span id="openObjectServices" class="input-group-addon fa fa-plus" data-fa-transform="left-22 down-10" onclick="openObjectServices();" title="Отвори картон на обекта"></span>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-cog fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<select class="form-control" name="nIDFirm" id="nIDFirm" onChange="formChange();" ></select>
					</div>
				</div>
			</div>
			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-cogs fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<select class="form-control" name="nIDOffice" id="nIDOffice" onChange="objChange();" ></select>
					</div>
				</div>
			</div>
		</div>

		<div class="modal-body pb-3">
			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-cube fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<select class="form-control" type="text" name="sType" id="sType" placeholder="Вид обслужване..." title="Вид" onChange="typeChange();" ></select>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-cube fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<select class="form-control" title="Основание" name="nIDReason" id="nIDReason" type="text" ></select>
					</div>
				</div>
			</div>
		</div>

		<div class="modal-body pb-3">
			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-cube fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<textarea title="Последно обслужване" placeholder="Последно обслужване" style="height: 30px; cursor: pointer;" class="ineligible green" name="sLastService" id="sLastService" onclick="openObjectServices();" readonly></textarea>
					</div>
				</div>
			</div>
		</div>
			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-cube fa-fw" data-fa-transform="right-22 down-10" title="Задача"></span>
						</div>
						<textarea class="h-50 form-horizontal" title="Забележка" name="sDescription" id="sDescription" placeholder="Забележка" ></textarea></textarea>
					</div>
				</div>
			</div>
		</div>

			{*<tr class="even">*}
				{*<td>Клиент:</td>*}
				{*<td>*}
					{*<input type="text" name="sRequestName" id="sRequestName" style="width: 238px;" />*}
				{*</td>*}
			{*</tr>*}

			{*<tr class="even">*}
				{*<td>Съставил:</td>*}
				{*<td>*}
					{*<input type="text" name="sCreatedUser" id="sCreatedUser" style="width: 238px;" readonly />*}
				{*</td>*}
			{*</tr>*}
			{**}
			{*<tr class="even">*}
				{*<td>Редактирал:</td>*}
				{*<td>*}
					{*<input type="text" name="sUpdatedUser" id="sUpdatedUser" style="width: 238px;" readonly />*}
				{*</td>*}
			{*</tr>*}

			{*<tr class="even">*}
				{*<td>Работна карта:</td>*}
				{*<td>*}
					{*<input type="text" name="nIDLimitCard" id="nIDLimitCard" style="width: 238px;" readonly />*}
				{*</td>*}
			{*</tr>*}
			{**}
			{*<tr class="even">*}
				{*<td align="right" style="width: 105px;">Планиран старт:&nbsp;</td>*}
				{*<td style="width: 180px;">*}
					{*<input type="text" name="sPlannedStartH" id="sPlannedStartH" style="width: 40px;" onkeypress="return formatTime(event);" maxlength="5" title="ЧЧ:ММ" />&nbsp;*}
					{*<input type="text" name="sPlannedStart" id="sPlannedStart" class="inp75" onkeypress="return formatDate(event, '.');" maxlength="10" title="ДД.ММ.ГГГГ" />&nbsp;*}
					{*<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgPlannedStart" />*}
				{*</td>*}
			{*</tr>*}
			{**}
			{*<tr class="even">*}
				{*<td colspan="2" align="left">Последно обслужване:</td>*}
			{*</tr>*}
			{**}
			{*<tr class="even">*}
				{*<td colspan="2" align="left">*}
					{*<textarea name="sLastService" id="sLastService" onclick="openObjectServices();" readonly style="border: 0px; width: 370px; height: 50px; background-color: transparent; color: rgb( 0, 155, 0 ); cursor: pointer;"></textarea>*}
				{*</td>*}
			{*</tr>*}
			{**}
			{*<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>*}
		{*</table>*}
		{**}
		{*<fieldset>*}
			{*<legend>Допълнителна информация</legend>*}
			{*<table class="input">*}
				{*<tr class="even">*}
					{*<td align="center">*}

						{*<textarea name="sDescription" id="sDescription" style="width: 325px; height: 55px;" /></textarea>*}
					{*</td>*}
				{*</tr>*}
				{*<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>*}
			{*</table>*}
		{*</fieldset>*}

		<div class="row mb-5">
			<div class="col-12 pl-1">
				&nbsp;
			</div>
		</div>
		<div id="search" class="modal-footer fixed-bottom mb-1 py-0">
			<button class="btn btn-sm btn-block btn-primary" type="submit" onClick="formSubmit();" class="search"> <i class="fas fa-check"></i> Запази </button>
			<button type="submit"></button>
		</div>
					{*<button type="button" onclick="formSubmitAndPlanning();" class="search"> Планирай </button>*}
					{*<button onClick="parent.window.close();"> Затвори </button>*}
	</form>
</div>

<script>
	loadXMLDoc2('load');
</script>