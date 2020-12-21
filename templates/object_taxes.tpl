{literal}
<script>
	rpc_debug = true;
	
	function editService(id) {
		var obj = document.getElementById('nID').value;
		dialogSetSetupObjectTaxes( id, obj )
	}
	
	function editService2(id) {
		var obj = document.getElementById('nID').value;
		dialogSetSetupObjectSingles( id, obj )
	}	

	function delService(id) {
		if ( confirm('Наистина ли желаете да премахнете записа?') ) {
			$('nIDRecord').value = id;
			loadXMLDoc2('deleteMonth', 1);
		}
	}

	function delService2(id) {
		if ( confirm('Наистина ли желаете да премахнете записа?') ) {
			$('nIDRecord2').value = id;
			var nID = $('nID').value;
			
			loadXMLDoc2('deleteSingle');
			
			rpc_on_exit = function() {
				window.location='page.php?page=object_taxes&nID='+nID+'&mobile=0';
				rpc_on_exit = function() {}	
			}			
		}
	}

	
	function addClient() {
		id = $('nID').value;
		id = parseInt(id);
		dialogClientObject(id);
		
		rpc_on_exit = function() {
			var nClient = $('nIDClient').value;
			
			if ( parseInt(nClient) > 0 ) {
				var client = $('sClient');
				client.onclick = function() {
					dialogClientInfo( parseInt(nClient) );
				}
			}
			
			rpc_on_exit = function() {}
		}		
	}

	function makePayment() {
		var id = $('nID').value;
		dialogSaleForObject(id);
	}
	

	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}		
</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="nIDClient" name="nIDClient" value="0" />
    <input type="hidden" id="nIDRecord" name="nIDRecord" value="0" />
    <input type="hidden" id="nIDRecord2" name="nIDRecord2" value="0" />


    {include file='object_tabs.tpl'}

    <div id="accordion">
        <div class="">
            <div class="nav nav-tabs navbar-dark bg-faded mb-1" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
						<i class="fa fa-expand fa-lg mr-2 ml-2"></i>Абонаментни такси
                    </button>
                </h5>
            </div>

            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                <div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"  style="height:385px; overflow: auto;"></div>
            </div>
        </div>
        <div class="">
            <div class="nav nav-tabs navbar-dark bg-faded mb-1" id="headingTwo">
                <h5 class="mb-0">
					<button class="btn btn-link collapsed float-left" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
						<i class="fa fa-expand fa-lg mr-2 ml-2"></i>Други задължения
					</button>
                </h5>
            </div>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                <div id="result2" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"  style="height:380px; overflow: auto;"></div>
            </div>
        </div>
    </div>

    <nav class="navbar fixed-bottom flex-row pt-3 pb-2 navbar-expand-lg" id="search">
        <div class="col-7 col-sm-7 col-lg-7 pl-0">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="fa fa-file fa-fw" data-fa-transform="right-22 down-10" itle="Клиент..."></span>
                </div>
                <input class="form-control" type="text" name="sClient" id="sClient" readonly />
                {if $edit.object_taxes_add_client}
                    <button class="btn btn-sm btn-dark" onClick="addClient();" title="Задай клиент..."><i class="fa fa-plus"></i> Клиент </button>
                {/if}

            </div>
        </div>
        <div class="col-5 col-sm-5 col-lg-5">
            <div class="input-group input-group-sm ml-0">
                {if $edit.object_taxes_month_obligations_edit}
                    <button id="b100" class="btn btn-sm btn-success mr-1" onClick="editService(0);" title="Добави абонамент!"><i class="fa fa-plus"></i> Абонамент </button>
                {/if}

                {if $edit.object_taxes_single_obligations_edit}
                    <button id="b100" class="btn btn-sm btn-success mr-1" onClick="editService2(0);" title="Добави задължение!"><i class="fa fa-plus"></i> Задължение </button>
                {/if}
                <button class="btn btn-sm btn-danger"	    onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
            </div>
        </div>
    </nav>

<div id="NoDisplay" style="display:none"></div>
</form>

<script>
	{literal}
		loadXMLDoc2('result');
		
		rpc_on_exit = function() {
			var nClient = $('nIDClient').value;
			
			if ( parseInt(nClient) > 0 ) {
				var client = $('sClient');
				client.onclick = function() {
					dialogClientInfo( parseInt(nClient) );
				}
			}
			
			rpc_result_area='result2';
			loadXMLDoc2('result2');
			rpc_result_area='result';
			rpc_on_exit = function() {}
		}
	{/literal}

//	{if !$edit.object_taxes_edit}{literal}
//		if ( form=document.getElementById('form1') ) {
//			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
//		}{/literal}
//	{/if}	
</script>