{literal}
	<script>
		rpc_debug = true;

		var filterVisible = false;

		InitSuggestForm = function() {
			for( var i = 0; i < suggest_elements.length; i++ ) {

				if ( suggest_elements[i]['id'] == 'sClient' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestClient );
				}

				if ( suggest_elements[i]['id'] == 'nNum' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestObjectNum );
				}
			}
		}

		function numKeyed( e ) {
			if ( e.keyCode && e.keyCode == 13 ) {
				loadXMLDoc('result');
			}
		}

		function onSuggestClient( aParams ) {
			var key		= aParams.KEY;
			var client	= new Array();
			client		= key.split(';;');

			if ( typeof(client[0]) != 'undefined' ) {
				$('nIDClient').value = client[0];
			} else {
				$('nIDClient').value = 0;
			}
		}

		function onSuggestObjectNum( aParams )
		{
			var sKey = aParams.KEY;
			var aKey = sKey.split( ";" );

			if( aKey[1] != "undefined" )
			{
				$("id_firm").value = 0;
				select_none( "aStatus" );
				onStatSelect();

				rpc_on_exit = function()
				{
					rpc_on_exit = function() {}

					$( "nNum" ).value = aKey[1];
					hideDiv( 1 );
				}

				loadXMLDoc( 'genregions' );
			}
		}


		function openObject( id ) {
			var sParams = new String();

			var aIDs = id.split( "," );
			var id = aIDs[0];

			if( parseInt( id ) )
				sParams = 'nID=' + id;
			else
				sParams = 'nID=' + id + '&id_f=' + $('id_firm').value + '&id_r=' + $('id_reg').value;

			dialogObjectInfo( sParams );
		}
		
		function openObjectContract( id )
		{
			var sParams = new String();
			
			var aIDs = id.split( "," );
			var id = aIDs[0];
			
			sParams = 'nID=' + id;
			
			dialogObjectContract( sParams );
		}
		
		function openObjectTaxes( id )
		{
			
			var aIDs = id.split( "," );
			var id = aIDs[0];
			
			sParams = 'nID=' + id;
			
			dialogObjectTaxes( sParams );
		}
		
		function openObjectArchiv( id )
		{
			
			var aIDs = id.split( "," );
			var id = aIDs[0];
			
			dialogObjectArchiv( id );
		}

        function openObjectMessages( id )
        {

            var aIDs = id.split( "," );
            var id = aIDs[0];

            dialogObjectMessages( id );
        }

		function openObjectStore( id )
		{
			var sParams = new String();
			
			var aIDs = id.split( "," );
			var id = aIDs[0];
			
			sParams = 'nID=' + id;
			
			dialogObjectStore( sParams );
		}
		
		function newObject()
		{
			var sParams = new String();
			
			sParams = 'nID=0' + '&id_f=' + $('id_firm').value + '&id_r=' + $('id_reg').value;
			
			dialogObjectInfo( sParams );
		}

        function changesHandler()
        {
            if( $("sClient").value == "" ) $('nIDClient').value = 0;

            //var myValue = document.getElementById( 'sTType' ).value
            //if( myValue == '' )document.getElementById( 'nType' ).value = 0;
        }

		function hideDiv( load ) {
			var e = document.getElementById("filter");
			var h = document.getElementById("hide");
			var s = document.getElementById("show");

			e.style.display	= "none";
			h.style.display = "none";
			s.style.display = "block";
			filterVisible	= false;

			if ( load == 1 ) {
				loadXMLDoc('result');
			}
		}

		function fixFilter() {
			var e = document.getElementById("filter");
			var h = document.getElementById("hide");
			var s = document.getElementById("show");

			switch( filterVisible ) {
				case true:
					hideDiv(1);
					break;

				case false:
					e.style.display	= "block";
					h.style.display = "block";
					s.style.display = "none";
					filterVisible	= true;
					break;
			}
		}
		
		function openFilter( type )
		{
			var id;
			if( type == 1 )
			{
				dialogObjectsFilter( 0 );
			}
			else
			{
				id = $('schemes').value;
				if( id != 0 )
				{
					dialogObjectsFilter( id );
				}
			}
		}
		
		function deleteFilter( schemes )
		{
			if( schemes.value > 0 )
			{
				if( confirm( 'Наистина ли желаете да премахнeте филтърът?' ) )
				{
					rpc_on_exit = function()
					{
						loadXMLDoc( 'generate' , 1 );
						
						rpc_on_exit = function() {}
					}
					
					loadXMLDoc( 'deleteFilter' );
				}
			}
		}

		function getKeyTest() {
			if ( event.keyCode == '13' ) {

				if ( filterVisible ) {
					hideDiv(1);
				}

				var num = parseInt($('nNum').value);

				if ( num > 0 ) {
					$("id_firm").value = 0;
					select_none("aStatus");
					onStatSelect();

					rpc_on_exit = function() {
						rpc_on_exit = function() {}

						$("nNum").value = num;
						hideDiv( 1 );
					}

					loadXMLDoc('genregions');
				}
			}
		}
		
		function viewClient( id )
		{
			var aIDs = id.split( "," );
			var id = aIDs[1];
			
			dialogClientInfo( id );
		}

        function onStatSelect()
        {
            var arSelected = new Array();
            //var selectElem = document.form1.elements["nStatus"];
            //var selectVal = selectElem[selectElem.selectedIndex];
            var Length = document.form1.aStatus.length;

            for(i=0;i<Length;i++)
            {

                if(document.form1.aStatus.options[i].selected )
                {

                    arSelected.push($('aStatus').options[i].innerHTML);
                }
            }
            if( $('nStatusInp') ) $('nStatusInp').innerHTML = arSelected.toString();

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

		function select_none( id )
		{
			oSEL = document.getElementById( id );
			if( oSEL.options.length )
			{
				for( i = 0; i < oSEL.options.length; i++ )
				{
					if( i == 0 ) oSEL.options[i].selected = true;
					else oSEL.options[i].selected = false;
				}
				return true;
			}
			else return false;
		}
	</script>
{/literal}

<dlcalendar click_element_id="sFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="sToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-setup-objects" onSubmit="return false;" onkeyup="getKeyTest();">
    <select onblur="hideStatusSelect(); onStatSelect();" onchange="onStatSelect();" class="ui-setup-objects-status-select" name="aStatus[]" size="8" id="aStatus" multiple></select>
    <input type="hidden" name="id" 			id="id" 		value="0"				/>
    {*<input type="hidden" name="nType" id="nType" value="0">*}
    <input type="hidden" name="nIDClient" 	id="nIDClient" 	value="0"				/>
    <input type="hidden" name="nDefReg" 	id="nDefReg" 	value="{$id_reg}"		/>
    <input type="hidden" name="nOpenWin" 	id="nOpenWin" 	value="{$nOpenWindow}"	/>
    <input type="hidden" name="nIDFirm" 	id="nIDFirm" 	value="{$id_firm}"		/>
    <input type="hidden" name="sPaidTo" 	id="sPaidTo" 	value="{$sPaidTo}"		/>
    <input type="hidden" name="nMode" 		id="nMode" 		value="{$nMode}"		/>

    {include file='tabs_setup_objects.tpl'}

    <div class="ui-setup-objects-filters">
        <div class="row justify-content-start table-secondary ui-setup-objects-toolbar">
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        {*Администрация:&nbsp;*}
                        <span class="ui-icon ui-icon-tag" title="Фирма на административно обслужване" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="id_firm" id="id_firm" onchange="loadXMLDoc( 'genregions' );" title="Фирма на административно обслужване"></select>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-tags" title="Офис на административно обслужване" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="id_reg" id="id_reg" ></select>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-home" title="Номер на обект" aria-hidden="true"></span>
                    </div>
                    <input class="form-control suggest" name="nNum" id="nNum" suggest="suggest" queryType="objByNumWithStatus" queryParams="" onkeypress="getKeyTest();" onkeyup="return numKeyed( event );" placeholder=" № на обект..." />

                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-home" title="Име на обекта" aria-hidden="true"></span>
                    </div>
                    <input class="form-control" name="sName" id="sName" placeholder="Име на обект..." />
                </div>
            </div>
            <div class="col-12 col-sm-8 col-lg-4">
                <div class="input-group input-group-sm">
                    <button type="button" id="hide" onclick="hideDiv(0);" class="btn btn-sm btn-light mr-2 ui-setup-objects-filter-toggle" style="display: none;" title="Скрий допълнителните филтри"><span class="ui-icon ui-icon-compress" aria-hidden="true"></span></button>
                    <button type="button" id="show" onclick="fixFilter();" class="btn btn-sm btn-light mr-2 ui-setup-objects-filter-toggle" title="Покажи допълнителните филтри"><span class="ui-icon ui-icon-expand" aria-hidden="true"></span></button>
                    {if $right_edit}
                        <button type="button" class="btn btn-sm btn-success mr-2" onclick="newObject();"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
                    {else}
                    {/if}
                    <button type="submit" name="Button" class="btn btn-sm btn-primary" onclick="hideDiv(1);"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси &nbsp;</button>
                </div>
            </div>
        </div>

        <div id="filter" style="display: none;" class="table-secondary ui-setup-objects-advanced-filter">

            <div class="row clearfix mb-1">
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-map" title="Адрес на обекта" aria-hidden="true"></span>
                        </div>
                        <input class="form-control" name="sAddress" id="sAddress" placeholder="Адрес на обекта..." />
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2 pl-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-phone" title="Телефон на обекта" aria-hidden="true"></span>
                        </div>
                        <input class="form-control" name="sPhone" id="sPhone" placeholder="0ххх..." />
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-user" title="МОЛ на обекта" aria-hidden="true"></span>
                        </div>
                        <input class="form-control" name="sMol" id="sMol" placeholder="МОЛ за обекта..." />
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2 pl-4-5">
                    <div class="input-group input-group-sm"  data-fa-transform="right-22 down-10" id="nStatusTd" onclick="showStatusSelect();">
                        <div class="form-control suggest" id="nStatusInp"></div>
                    </div>
                </div>
                <div class="col-12 col-sm-8 col-lg-4">
                    {if $right_nap}
                    <div class="btn-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-filter" title="Филтър" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="schemes" id="schemes" ></select>
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a href="#" class="dropdown-item dropdown-item-menu" name="Button5" id="objectsFilterAdd" title="Нов филтър" onClick="openFilter( 1 ); return false;">
                                <span class="ui-icon ui-icon-plus" aria-hidden="true"></span> &nbsp; Добави </a>
                            <a href="#" class="dropdown-item dropdown-item-menu" name="Button4" id="objectsFilterEdit" title="Редактиране на филтър" onClick="openFilter( 2 ); return false;">
                                <span class="ui-icon ui-icon-edit" aria-hidden="true"></span> &nbsp; Редактирай </a>
                            <a href="#" class="dropdown-item dropdown-item-menu" name="Button3" id="objectsFilterDelete" title="Премахване на филтър" onClick="deleteFilter( schemes ); return false;">
                                <span class="ui-icon ui-icon-delete" aria-hidden="true"></span> &nbsp; Изтрий </a>
                        </div>
                    </div>
                    {else}&nbsp;
                    {/if}
                </div>
            </div>

            <div class="row clearfix mb-1">
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm" title="Период на стартиране на обекта">
                        <div class="input-group-prepend">
                            {*Администрация:&nbsp;*}
                            <span class="ui-icon ui-icon-calendar" title="Период на стартиране" aria-hidden="true"></span>
                        </div>
                        <input type="text" name="sFromDate" id="sFromDate" class="form-control" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sFromDate}" />
                        <input type="text" name="sToDate" id="sToDate" class="form-control input-group-addon" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$sToDate}" />
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2 pl-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-tags" title="Неплатен абонамент за месец" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="sUnpaid" id="sUnpaid"></select>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-code" title="Дейност..." aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nFunction" id="nFunction" ></select>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-barcode" title="ЕИН на клиент" aria-hidden="true"></span>
                        </div>
                        <input class="form-control" name="sIDN" id="sIDN" placeholder="ЕИН на клиент..." />
                    </div>
                </div>
                <div class="col-12 col-sm-8 col-lg-4">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-location" title="Населено място..." aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nCity" id="nCity" ></select>
                    </div>
                </div>
            </div>

            <div class="row clearfix mb-2">
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-car" title="Фирма за реакция" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDReactionFirm" id="nIDReactionFirm" onchange="loadXMLDoc( 'genReactionOffices' );" title="Реагираща фирма"></select>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2 pl-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-car" title="Офис на реакция" aria-hidden="true"></span>
                        </div>
                        <select class="form-control"  name="nIDReactionOffice" id="nIDReactionOffice" title="Офис на реакция"></select>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-settings" title="Сервизна фирма" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDTechFirm" id="nIDTechFirm" onchange="loadXMLDoc( 'genTechOffices' );" title="Сервизна фирма"></select>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-settings" title="Сервизeн офис" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDTechOffice" id="nIDTechOffice" title="Сервизeн офис"></select>
                    </div>
                </div>
                <div class="col-6 col-sm-8 col-lg-4">
                    <div class="input-group input-group-sm">
                        <div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-compact btn-light mr-2" title="СОД">
                                <input type="checkbox" name="nIsSOD" id="nIsSOD" autocomplete="off" /><span class="ui-icon ui-icon-car" aria-hidden="true"></span>
                            </label>
                            <label class="btn btn-compact btn-light mr-2" title="С работно време">
                                <input type="checkbox" name="nIsWorkTime" id="nIsWorkTime" /><span class="ui-icon ui-icon-clock" aria-hidden="true"></span>
                            </label>
                            <label class="btn btn-compact btn-light mr-2" title="Без клиент">
                                <input type="checkbox" name="nHasNoClient" id="nHasNoClient" /><span class="ui-icon ui-icon-user-off" aria-hidden="true"></span>
                            </label>
                            <label class="btn btn-compact btn-light mr-2" title="Такси с ДДС">
                                <input type="checkbox" id="nDDS" name="nDDS" /><span class="ui-icon ui-icon-money" aria-hidden="true"></span>
                            </label>
                            <label class="btn btn-compact btn-light" title="В сервизен режим">
                                <input type="checkbox" name="nIsServiceMode" id="nIsServiceMode" /><span class="ui-icon ui-icon-wrench" aria-hidden="true"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="result" class="ui-setup-objects-result"></div>
         {*style=" max-height: calc(100% - 100px); margin-top: 0px !important; bottom: 0px; overflow-y: auto !important;  overflow-x: auto !important;"></div>*}
</form>

{literal}
	<script>
		rpc_on_exit = function()
		{
			{
				onStatSelect();
				loadXMLDoc( 'result' );
			}
			rpc_on_exit = function() {}
		}
		loadXMLDoc( 'generate' , 1 );

        if($('nOpenWin').value == 1)
        {
            hideDiv(1);
        }

        var statusSelect = document.getElementById('aStatus');
        var statusSelectTD = document.getElementById('nStatusInp');
        //
        // $( "*", document.body ).click(function( event ) {
        //     var offset = $( this ).offset();
        //     event.stopPropagation();
        //     $( "#nStatusInp" ).text( this.tagName +
        //         " coords ( " + offset.left + ", " + offset.top + " )" );
        // });

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
           // statusSelect.style.top =(statusSelectTD.clientHeight +  getAbsoluteTop(statusSelectTD))+'px' ;
           // statusSelect.style.left =(getAbsoluteLeft(statusSelectTD))+'px' ;
        }
		//nStatus

        var sel = $('aStatus');
        sel.addEventListener('change', function (e) {
			onStatSelect();

            e.preventDefault();
        }, false);
    </script>
{/literal}
