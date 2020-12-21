<script>
    rpc_debug = true;
</script>

<div class="modal-content pb-5">
    <form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save', 3 )">

        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция{else}Добавяне{/if} на зона</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-5">

            <input type="hidden" id="nID" name="nID" value="{$nID}">
            <input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObject}"	/>

            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fas fa-cubes fa-fw" data-fa-transform="right-22 down-10" title="Наименование на зона"></span>
                        </div>
                        <input class="form-control" type="text" name="sName" id="sName" placeholder="Наименование..." />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fa fa-barcode fa-fw" data-fa-transform="right-22 down-10" title="Номер на зона"></span>
                        </div>
                        <input class="form-control" type="text" name="nZone" id="nZone" placeholder="Номер на зона..." onkeypress="return formatDigits(event);" />
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-12 pl-1">
                &nbsp;
            </div>
        </div>

        <nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="input-group input-group-sm text-right">
                    <button class="btn btn-sm btn-block btn-primary" type="submit"><i class="fas fa-check"></i> Запази</button>
                </div>
            </div>
        </nav>
    </form>
</div>

<script>
    loadXMLDoc2( 'get' );
</script>