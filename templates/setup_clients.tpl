{literal}
	<script>
		rpc_debug = true;

        var filterVisible = false;

		function onInit()
		{
			loadXMLDoc2( 'load' );
		}
		
		function viewClient( id )
		{
			dialogClientInfo( id );
		}
		
		function openFilter( type )
		{
			var id;
			if( type == 1 )
			{
				dialogClientsFilter( 0 );
			}
			else
			{
				id = $('schemes').value;
				if( id != 0 )
				{
					dialogClientsFilter( id );
				}
			}
		}
		
		function deleteFilter( schemes )
		{
			if( schemes.value > 0 )
			{
				if( confirm( 'Наистина ли желаете да премахнeте филтърът?' ) )
				{
					loadXMLDoc2( 'deleteFilter', 6 );
				}
			}
		}
		
		function onFilterChange()
		{
			$("sName").value = "";
			$("sEIN").value = "";
			$("nID").value = "";
		}
		
		function onBasicFilterChange()
		{
			$("schemes").value = "0";
		}
		
		function enterConfirm()
		{
			if( event.keyCode == '13' )
			{
				loadXMLDoc2( 'result' );
			}
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
	</script>
{/literal}


<form action="" name="form1" id="form1" class="ui-client-report ui-client-admin" onsubmit="return false;">

    {include file='tabs_setup_objects.tpl'}

    <div class="ui-client-report-shell">
        <div class="row justify-content-start pl-3 pb-1 pt-2 table-secondary ui-client-report-filter">
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-handshake" aria-hidden="true" title="Тип на контрагента..."></span>
                    </div>
                    <select class="form-control"  name="client_type" id="client_type">
                        <option value="0"> - Всички -   </option>
                        <option value="1">Клиенти       </option>
                        <option value="2">Доставчици    </option>
                    </select>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        {*Администрация:&nbsp;*}
                        <span class="ui-icon ui-icon-tag" aria-hidden="true" title="Име на контрагент"></span>
                    </div>
                    <input class="form-control" type="text" id="sName" name="sName" onkeypress="onBasicFilterChange();" onkeyup="enterConfirm();" placeholder="Име на контрагент..."/>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-tags" aria-hidden="true" title="ЕИН/ЕГН на контрагент..."></span>
                    </div>
                    <input class="form-control" type="text" id="sEIN" name="sEIN" onkeypress="onBasicFilterChange();" onkeyup="enterConfirm();" placeholder="ЕИН/ЕГН на контрагент..." />
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-code" aria-hidden="true" title="Клиентски номер..."></span>
                    </div>
                    <input class="form-control" type="text" id="nID" name="nID" onkeypress="onBasicFilterChange();" onkeyup="enterConfirm();" placeholder="Клиентски номер..." />
                </div>
            </div>
            <div class="col-6 col-sm-8 col-lg-4 pl-3">
                <div class="input-group input-group-sm">
                    <button type="button" id="hide" onclick="hideDiv(0);" class="btn btn-sm btn-light mr-2" style="display: none;" title="Скрий разширения филтър"><span class="ui-icon ui-icon-compress" aria-hidden="true"></span></button>
                    <button type="button" id="show" onclick="fixFilter();" class="btn btn-sm btn-light mr-2" title="Покажи разширения филтър"><span class="ui-icon ui-icon-expand" aria-hidden="true"></span></button>
                    {if $right_edit}
                        <button type="button" class="btn btn-sm btn-success mr-2" onclick="viewClient( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
                    {else}
                    {/if}
                    <button class="btn btn-sm btn-primary" type="button" name="Button" onClick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button> <!--onclick="hideDiv(1);"-->
                </div>
            </div>
        </div>
        <div id="filter" style="display: none;" class="pl-3 pb-1 table-secondary ui-client-report-filter ui-client-advanced-filter">
            <div class="row clearfix mb-1">
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-user" aria-hidden="true" title="МОЛ..."></span>
                        </div>
                        <input class="form-control" type="text" name="sMOL" id="sMOL" placeholder="МОЛ..."/>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2 pl-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-location" aria-hidden="true" title="Адрес по регистрация..."></span>
                        </div>
                        <input class="form-control" type="text" name="sInvoiceAddress" id="sInvoiceAddress" placeholder="Адрес..."/>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-at" aria-hidden="true" title="E-mail..."></span>
                        </div>
                        <input class="form-control" type="text" name="sEmail" id="sEmail" placeholder="E-mail..."/>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-phone" aria-hidden="true" title="Телефон за контакт..."></span>
                        </div>
                        <input class="form-control" type="text" name="sPhone" id="sPhone" placeholder="Телефон..."/>
                    </div>
                </div>
                <div class="col-6 col-sm-8 col-lg-4">
                    <div class="input-group input-group-sm">
                    <div class="btn-group input-group-sm" role="group">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-filter" aria-hidden="true" title="Филтър"></span>
                        </div>
                        <select class="form-control" name="schemes" id="schemes" onchange="onFilterChange();"></select>
                        <button id="btnGroupDrop1" type="button" class="btn btn-compact btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item dropdown-item-menu" name="Button5" title="Нов филтър" onClick="openFilter( 1 );">Добави</a>
                            <a class="dropdown-item dropdown-item-menu" name="Button4" title="Редактиране на филтър" onClick="openFilter( 2 );">Редактирай</a>
                            <a class="dropdown-item dropdown-item-menu" name="Button3" title="Премахване на филтър" onClick="deleteFilter( schemes );">Изтрий</a>
                        </div>
                    </div>
                </div>
                </div>
                <div class="col-0 col-sm-0 col-lg-1"></div>
                <div class="col-0 col-sm-0 col-lg-1"></div>
            </div>
        </div>
    </div>

    <div id="result" class="ui-client-result"></div>
</form>

<script>
	onInit();
</script>
