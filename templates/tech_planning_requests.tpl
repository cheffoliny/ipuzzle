{literal}
	<script xmlns="http://www.w3.org/1999/html">
        rpc_debug = true;
        rpc_renderer_profile = "techPlanningRequest";

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

        function selectedPlanningRequestIds() {
            var inputs = document.querySelectorAll('#result input[name^="planning_f["]:checked');
            var ids = [];

            for (var index = 0; index < inputs.length; index++) {
                var match = /^planning_f\[(\d+)\]$/.exec(inputs[index].name || '');
                if (match && ids.indexOf(match[1]) === -1) {
                    ids.push(match[1]);
                }
            }

            return ids;
        }

        function syncPlanningRequestSelection() {
            var ids = selectedPlanningRequestIds().join(',');
            document.getElementById('id_request').value = ids;

            if (parent && parent.document) {
                var parentRequest = parent.document.getElementById('id_request');
                if (parentRequest) {
                    parentRequest.value = ids;
                }
            }
        }

        function setAllPlanningRequests(checked) {
            var inputs = document.querySelectorAll('#result input[name^="planning_f["]');
            for (var index = 0; index < inputs.length; index++) {
                inputs[index].checked = checked;
            }
            syncPlanningRequestSelection();
        }

        function just_do_it() {
            var action = document.getElementById('sel');
            if (!action) {
                return false;
            }

            if (action.value === 'mark_all') {
                setAllPlanningRequests(true);
            } else if (action.value === 'unmark_all') {
                setAllPlanningRequests(false);
            } else if (action.value === 'del') {
                if (!selectedPlanningRequestIds().length) {
                    alert('Изберете поне една задача.');
                    return false;
                }
                if (confirm('Наистина ли желаете да анулирате маркираните задачи?')) {
                    syncPlanningRequestSelection();
                    loadXMLDoc2('delRequest', 1);
                }
            }

            action.value = '';
            return false;
        }

        document.addEventListener('change', function (event) {
            if (event.target && /^planning_f\[\d+\]$/.test(event.target.name || '')) {
                syncPlanningRequestSelection();
            }
        });

	</script>

{/literal}
<form action="" name="form1" id="form1" class="ui-tech-planning-requests" onSubmit="return false;" role="form">
	<input type="hidden" name="id_request" id="id_request" value="0">
	<input type="hidden" name="id_office" id="id_office" value="{$nIDOffice}">
	<input type="hidden" name="id_contract" id="id_contract" value="0">

    <header class="ui-tech-request-toolbar">
        <div class="ui-tech-request-title"><span class="ui-icon ui-icon-list" aria-hidden="true"></span> Задачи</div>
        <div class="ui-tech-request-controls">
            <div class="ui-tech-request-field">
                <label for="nIDFirm">Фирма</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-building" aria-hidden="true"></span></div>
                    <select class="form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')"></select>
                </div>
            </div>
            <div class="ui-tech-request-field">
                <label for="nIDOffice">Сервизен офис</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-settings" aria-hidden="true"></span></div>
                    <select class="form-control" name="nIDOffice" id="nIDOffice" onchange="getResult();return false;"></select>
                </div>
            </div>
            <div class="ui-tech-request-field">
                <label for="nIDTechTiming">Вид обслужване</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-tags" aria-hidden="true"></span></div>
                    <select class="form-control" name="nIDTechTiming" id="nIDTechTiming" onchange="getResult();return false;">
                        <option value="0">-- Всички --</option>
                        {foreach from=$aTechTiming item=aType}
                            <option value="{$aType.id}">{$aType.description}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="ui-tech-request-field">
                <label for="sObjectName">Обект</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-home" aria-hidden="true"></span></div>
                    <input class="form-control" type="text" name="sObjectName" id="sObjectName" placeholder="Име или номер на обект" onchange="objChange();" />
                </div>
            </div>
        </div>
        <div class="ui-tech-request-actions">
            <button class="btn btn-sm btn-info" type="button" onclick="getResult();return false;"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
            <button class="btn btn-sm btn-success" type="button" onclick="editRequest(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Задача</button>
        </div>
    </header>

	<div id="result" class="ui-tech-planning-requests-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="on" rpc_autonumber="on"></div>

</form>

<script>
    onInit();
</script>
