{literal}
	<script>
		//rpc_debug = true;
		
		function submit_form() {
			loadXMLDoc( 'save', 0 );
		}
		
//		function delApplication(id) {
//			document.getElementById('id').value = id;
//			if ( confirm('Наистина ли желаете да премахнете молбата?') ){
//				loadXMLDoc('delete', 1);
//			}
//		}
		
		function setApplication(id) {
			var id_person = document.getElementById('id_person').value;
			//dialogSetApplication( id, person );
			dialogSetupPersonLeave( id, id_person );
		}
		
		function rpcEnd( oCallerHandle )
		{
			rpc_on_exit = function()
			{
				if( oCallerHandle )oCallerHandle.focus();
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc( "result" );
		}
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="id" name="id" value="{$id|default:0}" />
	<input type="hidden" id="id_person" name="id_person" value="{$id_person|default:0}" />
    <div class="modal-content pb-3">
        <div class="modal-header">
            Молби за отпуск
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body h-100">
			<div class="row mb-1">
				<div class="col ml-3">
					<div class="input-group input-group-sm">
						<label>Преглед по години</label>
						<input class="form-control" onkeypress="return formatDigits(event);" maxlength="4" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
						<button class="btn btn-sm btn-primary mx-1" type="button" onClick="loadXMLDoc('result'); return false;" name="Button"><i class="far fa-search"></i>Търси</button>
						<button class="btn btn-sm btn-success" id="b100" onClick="setApplication(0);"><i class="far fa-plus"></i>Добави</button>
					</div>
				</div>
			</div>

			<div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height:350px; overflow: auto;"></div>
			<!-- край на работната част -->
			<nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
				<div class="col-12">
					<div class="input-group input-group-sm">
						<button class="btn btn-block btn-sm btn-danger" onClick="parent.window.close();" ><i class="fa fa-times"></i> Затвори</button>
					</div>
				</div>
			</nav>
		</div>
	</div>
<div id="NoDisplay" style="display:none"></div>
</form>

<script>loadXMLDoc('result');//loadMainData();</script>
	{if !$edit_personnel}
		<script>
		if( form=document.getElementById('form1') )  
		//	for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		</script>
	{/if}