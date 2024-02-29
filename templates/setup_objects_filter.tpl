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
			rpc_on_exit = function()
			{
				if( window.opener.loadXMLDoc )window.opener.loadXMLDoc( "generate" );

				rpc_on_exit = function() {}
			}
			
			loadXMLDoc2( 'save' );
		}
	
	</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
    <div class="modal-content pb-3">
        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID} Редакция на{else} Добавяне на{/if} филтър</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

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
                        <input class="custom-control-input" type="checkbox" id="nMonthTax" name="nMonthTax"  />
                        <label class="custom-control-label" for="nMonthTax">Абонамент</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nUnpaidSingle" name="nUnpaidSingle" />
                        <label class="custom-control-label" for="nUnpaidSingle">Неплатени задължения</label>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox"  id="nLastPaid" name="nLastPaid" />
                        <label class="custom-control-label" for="nLastPaid">Платен месец</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nObjectFunction" name="nObjectFunction"  />
                        <label class="custom-control-label" for="nObjectFunction">Дейност</label>
                    </div>
                </div>
            </div>
            {*<input type="checkbox" id="nObjectType" name="nObjectType" class="clear" />Тип*}

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nObjectPhone" name="nObjectPhone" />
                        <label class="custom-control-label" for="nObjectPhone">Телефон</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nStartDate" name="nStartDate"  />
                        <label class="custom-control-label" for="nStartDate">Въведен</label>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nCityC" name="nCityC" />
                        <label class="custom-control-label" for="nCityC">Населено място</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">

                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nAddress" name="nAddress" />
                        <label class="custom-control-label" for="nAddress">Адрес</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nDistance" name="nDistance" />
                        <label class="custom-control-label" for="nDistance">Дистанция</label>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nOperativeInfo" name="nOperativeInfo" />
                        <label class="custom-control-label" for="nOperativeInfo">Оперативна информация</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nReactReg" name="nReactReg"  />
                        <label class="custom-control-label" for="nReactReg">Реагиращ офис</label>
                    </div>
                </div>
            </div>
            {*<input type="checkbox" id="nEndContract" name="nEndContract" class="clear" />	*}
            <div class="row mb-2">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nWorkTime" name="nWorkTime"/>
                        <label class="custom-control-label" for="nWorkTime">Работно време</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nAdminReg" name="nAdminReg" />
                        <label class="custom-control-label" for="nAdminReg">Административен офис</label>
                    </div>
                </div>
            </div>

            <div class="row mb-5 pb-5">
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" name="nTech" id="nTech" />
                        <label class="custom-control-label" for="nTech">Техника</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="custom-control custom-checkbox ">
                        <input class="custom-control-input" type="checkbox" id="nTechReg" name="nTechReg"  />
                        <label class="custom-control-label" for="nTechReg">Сервизен офис</label>
                    </div>
                </div>
            </div>

        </div>
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
