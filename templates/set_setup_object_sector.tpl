<script>
    rpc_debug = true;
    rpc_html_debug = true;
</script>
<form action="" method="POST" name="form1" id="form1" class="ui-sod-editor ui-sector-editor" onsubmit="loadXMLDoc2( 'save', 3 ); return false;">
    <div class="modal-content ui-sod-editor-content">

        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция{else}Добавяне{/if} на сектор</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body ui-sod-editor-body">

            <input type="hidden" id="nID"       name="nID"          value="{$nID}"      />
            <input type="hidden" id="nIDObject" name="nIDObject"    value="{$nIDObject}"/>

            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-cube" aria-hidden="true" title="Наименование на сектор"></span>
                        </div>
                        <input class="form-control" type="text" name="sName" id="sName" placeholder="Наименование..." />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-barcode" aria-hidden="true" title="Номер на сектор"></span>
                        </div>
                        <input class="form-control" type="text" name="nSector" id="nSector" placeholder="Номер на сектор..." onkeypress="return formatDigits(event);" />
                    </div>
                </div>
            </div>
        </div>
        <nav id="search" class="modal-footer fixed-bottom ui-sod-editor-actions" aria-label="Действия със сектора">
            <button class="btn btn-sm btn-primary" type="submit"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запази</button>
            <button class="btn btn-sm btn-danger" type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
        </nav>
    </div>
</form>

<script>
    loadXMLDoc2( 'get' );
</script>
