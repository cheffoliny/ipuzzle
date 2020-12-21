{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('load');
		}
		
		function saveForm() {
			loadXMLDoc2('save',3);
		}
		
	</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
    <div class="modal-content p-2">
            <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция{else}Добавяне{/if} на лице за контакт</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">


            <input type="hidden" id="nID" name="nID" value="{$nID}">
            <input type="hidden" id="id_obj" name="id_obj" value="{$id_obj}">
            <input type="hidden" id="id_face" name="id_face" value="0">

            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="far fa-user fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
                        </div>
                        <input class="form-control" type="text" name="sName" id="sName" placeholder="Име..." />
                    </div>
                </div>
            </div>


            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fab fa-viber fa-fw" data-fa-transform="right-22 down-10" title="Телефон за връзка..."></span>
                        </div>
                        <input class="form-control" type="text" name="sPhone" id="sPhone" placeholder="Телефон за връзка..." />
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fab fa-viber fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
                        </div>
                        <input class="form-control" type="text" name="sPost" id="sPost" placeholder="Длъжност..." />
                    </div>
                </div>
            </div>

            <div class="col-auto my-2">
                <div class="custom-control custom-checkbox ">
                    <input class="custom-control-input" type="checkbox" name="isMOL" id="isMOL" />
                    <label class="custom-control-label" for="isMOL">Отговорник &nbsp;</label>
                </div>
            </div>

        </div>
        <div class="modal-footer mb-0">
            <button class="btn btn-sm btn-block btn-primary" onclick="saveForm();"><i class="fas fa-check"></i> Запиши </button>
        </div>
    </div>
</form>






<script>
	onInit();
</script>