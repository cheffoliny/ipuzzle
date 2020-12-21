{literal}
	<script>
		rpc_debug = true;
		rpc_method = 'POST';
		
		function onInit() {
			loadXMLDoc2( 'result' );
		}

		function annulment() {
			if ( confirm("Наистина ли желаете да анулирате документа?") ) {
				loadXMLDoc2('annulment', 1);

				rpc_on_exit = function() {
					window.location.reload();
				}
			}
		}	
		
	</script>
{/literal}


<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="sDocType" name="sDocType" value="{$sDocType}" />
	<input type="hidden" id="nIDDoc" name="nIDDoc" value="{$nIDDoc}" />

	<div class="modal-content p-2">
		<div class="modal-header">
			{if $sDocType eq 'buy'}
				<h6 class="modal-title text-white">{$sOrderTypeCaption} Ордер №: {$nOrderNum} - {$sOrderDate} {if $status eq 'canceled'} [АНУЛИРАН]{elseif $status eq 'opposite'} [НАСРЕЩЕН]{/if}</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
					<span aria-hidden="true">&times;</span>
				</button>
			{else}
				<h6 class="modal-title text-white">{$sOrderTypeCaption} Ордер №: {$nOrderNum} - {$sOrderDate} {if $status eq 'canceled'} [АНУЛИРАН]{elseif $status eq 'opposite'} [НАСРЕЩЕН]{/if}</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
					<span aria-hidden="true">&times;</span>
				</button>
			{/if}
		</div>


		{include file='order_tabs.tpl'}

		<div class="modal-body" id="filter">

			<div class="row my-1" id="result" rpc_paging="off" rpc_excel_panel="off" rpc_resize="off" style="height: 600px; overflow: auto;"></div>
		</div>

		<div class="fixed-bottom text-center p-2">
			{if $grant_right and $status eq "active"}
				<button class="btn btn-block btn-danger py-1" onClick="annulment(); return false;"><i class="far fa-times"></i>&nbsp; Анулирай </button>
			{/if}
		</div>

	</form>

<script>
	onInit();
</script>