{literal}
<script>
	rpc_debug=true;
	
	var my_action = '';
	
	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'ClientName' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestClientName );
			}
			
			if( suggest_elements[i]['id'] == 'ClientPhone' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestClientPhone );
			}			
		}
	}
			
	function onSuggestClientName ( aParams ) {
		var key = aParams.KEY;
		var arr = key.split(';;');
		
		if ( arr[0].length > 0 ) {
			$('nID').value = parseInt( arr[0] );
		}
		
		if ( arr[2].length > 0 ) {
			$('ClientPhone').value = arr[2];
		}		
	}
	
	function onSuggestClientPhone ( aParams ) {
		var key = aParams.KEY;
		var arr = key.split(';;');
		
		if ( arr[0].length > 0 ) {
			$('nID').value = parseInt( arr[0] );
		}
		
		if ( arr[1].length > 0 ) {
			$('ClientName').value = arr[1];
		}		
	}

    function onClientNameChange() {
        if(!empty(jQuery('#ClientName').val())) {
            $('nID').value = 0;
        }
    }
	
	
	function formSubmit() {
		loadXMLDoc2( 'save', 3 );
	}
		
</script>
{/literal}


<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">

    <div class="modal-content pb-3">
        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID} Редакция на{else} Добавяне на{/if} клиент</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-5">

            <input type="hidden" id="nID" name="nID" value="0" />
            <input type="hidden" id="nIDObject" name="nIDObject" value="{$id}" />


            <div class="row mb-2">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fas fa-user fa-fw" data-fa-transform="right-22 down-10" title="Име на клиент..."></span>
                        </div>
                        <input class="form-control" id="ClientName" name="ClientName" type="text" suggest="suggest" queryType="ClientName" queryParams="nIDObject" onchange="onClientNameChange()" onpast="onClientNameChange()" />
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fab fa-viber fa-fw" data-fa-transform="right-22 down-10" title="Телефон на клиент..."></span>
                        </div>
                        <input class="form-control" id="ClientPhone" name="ClientPhone" type="text" suggest="suggest" queryType="ClientPhone" onchange="onClientNameChange()" onpast="onClientNameChange()" />
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-12 pl-1">
                    &nbsp;
                </div>
            </div>

        </div>
    </div>

    <nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="input-group input-group-sm text-right">
                <button class="btn btn-sm btn-block btn-primary" onClick="formSubmit();"><i class="fas fa-check"></i> Запази </button>
            </div>
        </div>
    </nav>

</form>

{literal}
	<script>
		loadXMLDoc2('result');
	</script>
{/literal}
