{literal}
<script>
    rpc_debug = true;
    rpc_html_debug = true;

    function formChange(type) {
        if (type === 'firm') {
            document.getElementById('nIDOffice').value = 0;
        }
        loadXMLDoc2('load', 0);
    }

    function formSubmit() {
        rpc_on_exit = function(errorCode) {
            rpc_on_exit = function() {};
            if (errorCode) {
                return;
            }

            if (window.opener && !window.opener.closed && typeof window.opener.test === 'function') {
                window.opener.test();
            }
            parent.window.close();
        };
        loadXMLDoc2('save', 0);
    }
</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" class="ui-person-editor ui-limit-card-person-editor" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID}">
    <input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard}">

    <div class="modal-content ui-person-editor-content">
        <div class="modal-header">
            <h6 class="modal-title text-white">{if $nID}Редакция на{else}Нов{/if} служител</h6>
            <button type="button" class="close" aria-label="Затвори" onClick="parent.window.close();"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body ui-person-editor-body">
            <div class="input-group input-group-sm mb-2">
                <div class="input-group-prepend"><span class="ui-icon ui-icon-building" aria-hidden="true" title="Фирма"></span></div>
                <select class="form-control" name="nIDFirm" id="nIDFirm" onChange="formChange('firm');"></select>
            </div>
            <div class="input-group input-group-sm mb-2">
                <div class="input-group-prepend"><span class="ui-icon ui-icon-location" aria-hidden="true" title="Регион"></span></div>
                <select class="form-control" name="nIDOffice" id="nIDOffice" onChange="formChange('office');"></select>
            </div>
            <div class="input-group input-group-sm mb-2">
                <div class="input-group-prepend"><span class="ui-icon ui-icon-user" aria-hidden="true" title="Служител"></span></div>
                <select class="form-control" name="nIDPerson" id="nIDPerson"></select>
            </div>
            <div class="ui-person-editor-inline">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-percent" aria-hidden="true" title="Процент"></span></div>
                    <input class="form-control" type="text" name="nPercent" id="nPercent" maxlength="3" onkeydown="return formatNumber(event);" placeholder="Процент">
                </div>
                <label class="ui-person-toggle" for="all"><input type="checkbox" id="all" name="all" onClick="formChange('office')"><span>Всички служители</span></label>
            </div>
        </div>
        <nav class="modal-footer fixed-bottom ui-person-editor-actions" aria-label="Действия със служителя">
            <button type="button" class="btn btn-sm btn-primary" onClick="formSubmit();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
            <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
        </nav>
    </div>
</form>

<script>
    loadXMLDoc2('load');
</script>
