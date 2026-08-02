{literal}
<script>
    rpc_debug = true;
    rpc_html_debug = true;

    function submitForm() {
        loadXMLDoc2('save', 3);
    }
</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" class="ui-person-editor ui-person-shift-editor" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID}">

    <div class="modal-content ui-person-editor-content">
        <div class="modal-header">
            <h6 class="modal-title text-white">{if $nID}Редакция на{else}Нова{/if} смяна</h6>
            <button type="button" class="close" aria-label="Затвори" onClick="parent.window.close();"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body ui-person-editor-body">
            <div class="input-group input-group-sm mb-2">
                <div class="input-group-prepend"><span class="ui-icon ui-icon-code" aria-hidden="true" title="Код"></span></div>
                <input class="form-control" type="text" name="sCode" id="sCode" placeholder="Код">
            </div>
            <div class="input-group input-group-sm mb-2">
                <div class="input-group-prepend"><span class="ui-icon ui-icon-name" aria-hidden="true" title="Наименование"></span></div>
                <input class="form-control" type="text" name="sName" id="sName" placeholder="Наименование">
            </div>
            <div class="ui-person-shift-period">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-clock" aria-hidden="true" title="От час"></span></div>
                    <input class="form-control" type="text" name="sShiftFrom" id="sShiftFrom" onKeyPress="return formatTime(event);" maxlength="6" placeholder="От">
                </div>
                <span>до</span>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-clock" aria-hidden="true" title="До час"></span></div>
                    <input class="form-control" type="text" name="sShiftTo" id="sShiftTo" onKeyPress="return formatTime(event);" maxlength="6" placeholder="До">
                </div>
            </div>
            <div class="input-group input-group-sm mt-2">
                <div class="input-group-prepend"><span class="ui-icon ui-icon-info" aria-hidden="true" title="Описание"></span></div>
                <textarea class="form-control" name="sDescription" id="sDescription" rows="4" placeholder="Допълнителна информация"></textarea>
            </div>
        </div>
        <nav class="modal-footer fixed-bottom ui-person-editor-actions" aria-label="Действия със смяната">
            <button type="button" class="btn btn-sm btn-primary" onclick="submitForm();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
            <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
        </nav>
    </div>
</form>

<script>
    loadXMLDoc2('load');
</script>
