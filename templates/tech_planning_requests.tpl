{literal}
	<script xmlns="http://www.w3.org/1999/html">
        rpc_debug = true;
        rpc_xsl = "xsl/tech_planning_request.xsl";

        //        InitSuggestForm = function() {
        //            for(var i = 0; i < suggest_elements.length; i++) {
        //                if( suggest_elements[i]['id'] == 'sObjectName' ) {
        //                    suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
        //                }
        //            }
        //        };

        function onInit() {
            loadXMLDoc2('load');
        }

        function getResult() {
            parent.document.getElementById('id_request_office').value = $('nIDOffice').value;
            loadXMLDoc2('result');
//			}
        }

        rpc_on_exit = function ( nCode ) {
            if( !parseInt( nCode ) ) {
//				document.getElementById('empty').focus();
                document.getElementById('id_request').value = '0';
                parent.document.getElementById('id_request').value = '0';
            }
        };

        function openRequest(id) {
            dialogTechRequest(id);
        }

        function openContract(id_contract) {
            $('id_contract').value = id_contract;
//			loadDirect('export_to_pdf');
            window.open('page.php?page=sales_contract&docNumber='+id_contract+'&type=contract&is_window=3', 'Договор');
        }

        function openObject(id) {
            dialogObjectInfo('nID='+id);
        }

        function delRequest( id )
        {
            $('id_request').value = id;
            if( confirm('Наистина ли желаете да анулирате Задачата?') )
            {
                loadXMLDoc2( 'delRequest', 1 );
            }
        }

        function planRequest( id ) {
            dialogPlanRequest(id);
        }

        function getMap() {
            if ( $('nIDOffice').value != 0 ) {
                var id = $('nIDOffice').value;

                dialogObjectsMap(id);
            }
            //objects_map
        }

        function editRequest(id) {
            if ( id.length > 1 ) {
                var ids = id.split(',');
                dialogTechRequest(ids[0]);
            } else dialogTechRequest(id);
        }

        function openTp(params) {

            var aP = params.split('@');

            dialogNewTp(aP[0], aP[1]);
        }

        //        function onSuggestObject(aParams) {
        //            $('nIDObject').value = aParams.KEY;
        //        }

        function objChange() {
            $sObjectName = document.getElementById('sObjectName').value;
        }

	</script>

{/literal}
<form action="" name="form1" id="form1" onSubmit="return false;" role="form">
	<input type="hidden" name="id_request" id="id_request" value="0">
	<input type="hidden" name="id_office" id="id_office" value="{$nIDOffice}">
	<input type="hidden" name="id_contract" id="id_contract" value="0">

    <div class="row justify-content-start pb-1 pt-1 nav-intelli text-white"">
        <div class="col-6 col-sm-1 col-lg-1 mt-1 ml-3">
            <h6 class="text-white"><i class="fas fa-tasks"></i> Задачи </h6>
        </div>
        <div class="col-6 col-sm-2 col-lg-2">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></span>
                </div>
                <select class="form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" ></select>
            </div>
        </div>

        <div class="col-6 col-sm-2 col-lg-2">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="fa fa-cogs fa-fw" data-fa-transform="right-22 down-10" title="Сервизeн офис"></span>
                </div>
                <select class="form-control" name="nIDOffice" id="nIDOffice" onchange="getResult();return false;" ></select>
            </div>
        </div>

        <div class="col-6 col-sm-2 col-lg-2">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="fa fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Вид обслужване"></span>
                </div>
                <select class="form-control" name="nIDTechTiming" id="nIDTechTiming" onchange="getResult();return false;">
                    <option value="0">-- Всички --</option>
                    {foreach from=$aTechTiming item=aType}
                        <option value="{$aType.id}">{$aType.description}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="col-6 col-sm-2 col-lg-2">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="fa fa-home fa-fw" data-fa-transform="right-22 down-10" title="Обект..."></span>
                </div>
                <input class="form-control" type="text" name="sObjectName" id="sObjectName" placeholder="Обект..." onchange="objChange();" />
                {*<input type="hidden" name="nIDObject" id="nIDObject" />*}
            </div>
        </div>

        <div class="col-6 col-sm-2 col-lg-2">
            <div class="input-group input-group-sm">
                <button class="btn btn-sm btn-info"     type="button" onClick="getResult();return false;"><i class="fa fa-search fa-lg"></i> Търси&nbsp;</button>
                <button class="btn btn-sm btn-success ml-1" onClick="editRequest(0);"><i class="fa fa-plus fa-lg"></i> Задача </button>
                {*<button class="btn btn-xs btn-warning"  type="button" onClick="getMap();"       ><span class="fa fa-map-marker"          ></span> Карта&nbsp;&nbsp;&nbsp;</button>*}
            </div>
        </div>
    </div>

	<div id="result" rpc_excel_panel="off" rpc_paging="off" class="container-fluid body-content"></div>

</form>

<script>
    onInit();
</script>