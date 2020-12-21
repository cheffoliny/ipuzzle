<script>
    rpc_debug = true;
</script>
<div class="modal-content pb-5">
    <form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save', 3 )">

        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция{else}Добавяне{/if} на сектор</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-5">

            <input type="hidden" id="nID"       name="nID"          value="{$nID}"      />
            <input type="hidden" id="nIDObject" name="nIDObject"    value="{$nIDObject}"/>

            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fas fa-cube fa-fw" data-fa-transform="right-22 down-10" title="Наименование на сектор"></span>
                        </div>
                        <input class="form-control" type="text" name="sName" id="sName" placeholder="Наименование..." />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Номер на сектор"></span>
                        </div>
                        <input class="form-control" type="text" name="nSector" id="nSector" placeholder="Номер на сектор..." onkeypress="return formatDigits(event);" />
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-12 pl-1">
              &nbsp;
            </div>
        </div>
        <div id="search" class="modal-footer fixed-bottom mb-1 py-0">
            <button class="btn btn-sm btn-block btn-primary" type="submit"><i class="fas fa-check"></i> Запази</button>
        </div>
    </form>
</div>

<script>
    loadXMLDoc2( 'get' );
</script>