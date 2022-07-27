{literal}
    <script>
        rpc_debug = true;

        InitSuggestForm = function() {
            for( var i = 0; i < suggest_elements.length; i++ ) {

//                if ( suggest_elements[i]['id'] == 'sClient' ) {
//                    suggest_elements[i]['suggest'].setSelectionListener( onSuggestClient );
//                }

                if ( suggest_elements[i]['id'] == 'sObjName' ) {
                    suggest_elements[i]['suggest'].setSelectionListener( onSuggestObjectNum );
                }
            }
        };


        function onSuggestObjectNum( aParams )
        {
            var sKey = aParams.KEY;
            var aKey = sKey.split( ";" );

            jQuery('#nIDObject').val(aKey[0]);
        }

        function resetObject() {
            if(empty( jQuery('sObjName').val() )) {
                jQuery('#nIDObject').val(0);
            }
        }


        function openClient(params)
        {
            var el = params.split('@');

            if(el[1] != 0)
                dialogClientInfo(el[1]);
        }

        function openObject(params){
            var el = params.split('@');

            if(el[2] != 0)
                dialogObjectInfo( 'nID='+el[2]);
        }

        function tab_href( sPageID )
        {
            $( sPageID ).href = "page.php?page="+ sPageID;
            return true;
        }

        function onselect()
        {
            var arSelected = [];
            var arSelectedVals = [];
            //var selectElem = document.form1.elements["nStatus"];
            //var selectVal = selectElem[selectElem.selectedIndex];
            var Length = document.form1.aStatus.length;

            for(i=0;i<Length;i++)
            {

                if(document.form1.aStatus.options[i].selected )
                {
                    arSelected.push($('aStatus').options[i].innerHTML);
                    arSelectedVals.push($('aStatus').options[i].value);
                }
            }
            if( $('nStatusInp') ) $('nStatusInp').innerHTML = arSelected.toString();
            if( $('StatusInpVal') ) $('StatusInpVal').value = arSelectedVals.toString();
            return true;
        }

        function getAbsoluteTop(obj) {
            var top = obj.offsetTop;

            if( typeof( obj.offsetParent ) != "undefined" && obj.offsetParent != null )
            {
                var parent = obj.offsetParent;

                while (parent != document.body)
                {
                    top += parent.offsetTop;
                    top -= parent.scrollTop;
                    parent = parent.offsetParent;
                }
            }

            return top;
        }

        function getAbsoluteLeft(obj) {
            var left = obj.offsetLeft;
            var parent = obj.offsetParent;
            while (parent != document.body) {
                left += parent.offsetLeft;
                parent = parent.offsetParent;
            }
            return left;
        }

        function showStatusSelect()
        {
            statusSelect.style.display='';
            statusSelect.focus();

        }

        function hideStatusSelect()
        {
            statusSelect.style.display='none';
        }

    </script>
{/literal}

<dlcalendar click_element_id="sDateFrom" input_element_id="sDateFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="sDateTo" input_element_id="sDateTo" tool_tip="Изберете дата"></dlcalendar>


{*<dlcalendar click_element_id="sFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>*}
{*<dlcalendar click_element_id="sToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>*}
<form id="form1" name="form1" onsubmit="return false;" >
    <select onblur="hideStatusSelect(); onselect();" onchange="onselect();" style="display: none; position: absolute;" class="select200" name="aStatus[]" size="8" id="aStatus" multiple>
        <option value="0">-Всички статуси-</option>
        <option value="wait">изчакване</option>
        <option value="sending">в процес на изпращане</option>
        <option value="sent">изпратено</option>
        <option value="failed">неуспешно изпращане</option>
        <option value="canceled">отказано</option>
    </select>
    <input type="hidden" name="nIDObject" id="nIDObject" />
    <input type="hidden" name="StatusInpVal" id="StatusInpVal"/>
    <input type="hidden" name="nStatusInp"  id="nStatusInp"  />
    <input type="hidden" name="sChannel" id="sChannel" value="0" />
    <input type="hidden" name="sPhone" id="sPhone" />
    {include file='tabs_setup_objects.tpl'}

    <div>
        <div class="row justify-content-start pl-3 pb-1 pt-2 table-secondary">
            <div class="col">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="far fa-handshake fa-fw" data-fa-transform="right-22 down-10" title="Тип на контрагента..."></span>
                    </div>
                    <select class="form-control"  id="nNotificationsEvents" name="nNotificationsEvents">
                        <option value="0">-Всички събития-</option>
                        {foreach from=$aEvents item=event}
                            <option value="{$event.id}">{$event.description}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        {*Администрация:&nbsp;*}
                        <span class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
                    </div>
                    <input class="form-control" type="text" name="sClientName" id="sClientName" placeholder="Име на контрагент..."/>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <i class="fa fa-home fa-fw"  data-fa-transform="right-22 down-10" title="Номер на обект"></i>
                    </div>
                    <input class="form-control suggest" name="sObjName" id="sObjName" suggest="suggest"  querytype="objByNumWithStatus" queryparams="" onchange="resetObject()" placeholder=" № на обект..." ">

                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                    <div class="input-group-prepend">
                        {*Администрация:&nbsp;*}
                        <span id="editFromDate" id="editToDate" class="fas fa-calendar-alt fa-fw" data-fa-transform="right-22 down-10" ></span>
                    </div>
                    <input type="text" name="sDateFrom" id="sDateFrom" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
                    <input type="text" name="sDateTo" id="sDateTo" class="form-control input-group-addon" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
                    {*<div class="input-group-append-r">*}
                    {*<span id="editToDate" class="fa fa-calendar-plus-o" title="Край на периода"></span>*}
                    {*</div>*}
                </div>
            </div>

            <div class="col-12 col-sm-8 col-lg-4">
                <div class="input-group input-group-sm">
                    <button name="button" class="btn btn-sm btn-info" onclick="loadXMLDoc2( 'result' );"><i class="far fa-search"></i>&nbsp;Търси</button>
{*                    <button name="button" class="btn btn-sm btn-success" onclick="dialogAddNotification()"><i class="far fa-plus"></i>&nbsp;Добави</button>*}
                </div>
            </div>
        </div>
        <div class="row clearfix mb-1">
{*            <div class="col">*}
{*                <div class="input-group input-group-sm">*}
{*                    <div class="input-group-prepend">*}
{*                        <span class="far fa-handshake fa-fw" data-fa-transform="right-22 down-10" title="Тип на контрагента..."></span>*}
{*                    </div>*}
{*                    <select class="form-control" id="sChannel" name="sChannel">*}
{*                        <option value="0">-- Всички канали --</option>*}
{*                        <option value="sms">SMS</option>*}
{*                        <option value="mail">email</option>*}
{*                        <option value="tel">телефон</option>*}
{*                        <option value="system">система</option>*}
{*                    </select>*}
{*                </div>*}
{*            </div>*}
            <div class="col">
                <div class="input-group-prepend">
                    {*Администрация:&nbsp;*}
                    <span class="fas fa-home fa-fw" data-fa-transform="right-22 down-10" title="Телефон"></span>
                </div>
                </div>

            <div class="col">

            </div>
        </div>
    </div>
    <div id="result"></div>
</form>

{literal}

    <script>

        var statusSelect = document.getElementById('aStatus');
        var statusSelectTD = document.getElementById('nStatusInp');
        function deleteObject( id )
        {
            if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
            {
                $('id').value = id;
                loadXMLDoc( 'delete' );
            }
        }

        if( statusSelectTD )
        {
            statusSelect.style.top =(statusSelectTD.clientHeight +  getAbsoluteTop(statusSelectTD))+'px' ;
            statusSelect.style.left =(getAbsoluteLeft(statusSelectTD))+'px' ;
        }
        //nStatus
        var sel = $('aStatus');
        sel.addEventListener('change', function (e) {
            onselect();

            e.preventDefault();
        }, false);
    </script>
{/literal}
