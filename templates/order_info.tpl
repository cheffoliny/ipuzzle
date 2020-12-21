{literal}
	<script>
		rpc_debug 		= true;
		rpc_autonumber 	= "off";
		rpc_method 		= "POST";

		function LPAD( sNum, nSpaces, nFillout ) {
			var nIterations = nSpaces - sNum.length;
			var sPrefix = "";
						
			if ( sNum.length > nSpaces ) {
				return sNum;
			}
			
			for ( i = 0; i < nIterations; i++ ) {
				sPrefix += nFillout;
			}
			
			return sPrefix + sNum;
		}
		
		function onInit() {
			//Disable Stuff
			if ( $("nID").value != 0 ) {
				$("nTotalSum").disabled 		= "disabled";
				$("sNote").disabled 			= "disabled";
				$("nIDBankAccount").disabled 	= "disabled";
				$("nPaidSum").disabled 			= "disabled";
			}
			
			rpc_on_exit = function() {
				if ( document.getElementById("nDocNum") ) {
					$("nDocNum").value = LPAD( $("nDocNum").value, 10, 0 );
				}
				
				rpc_on_exit = function() {};
			};
			
			loadXMLDoc2("load");
		}
		
		function sumResto() {
			var nSum 		= parseFloat( $('nTotalSum').value );
			var nCashSum 	= parseFloat( $('nPaidSum').value );
			var nResto 		= nCashSum - nSum;
			
			nResto 			= Math.round( nResto * 100 ) / 100;
			nResto 			= nResto.toFixed( 2 );
			
			if ( nResto >= 0 ) {
				$('nRestSum').value = nResto;
			} else {
				$('nRestSum').value = '0.00';
			}
		}
		
		function openDoc() {
			var id 			= $("nIDDoc").value;
			var doc_type 	= $("sDocType").value;
			
			if ( id ) {
				switch(doc_type) {
					case "buy":
						dialogBuy('id='+id, 'buy_'+id);
					break;
					
					case "sale":
						dialogSale2(id);
					break;
				}
			}
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
	<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />

	<input type="hidden" id="sOrderType" name="sOrderType" value="{$sOrderType}" />
	<input type="hidden" id="nOrderNum" name="nOrderNum" value="{$nOrderNum}" />
	<input type="hidden" id="sDocType" name="sDocType" value="{$sDocType}" />
	<input type="hidden" id="nIDDoc" name="nIDDoc" value="{$nIDDoc}" />
	<input type="hidden" id="sAccountType" name="sAccountType" value="{$sAccountType}" />
	<input type="hidden" id="bAllowView" name="bAllowView" value="{$bView}" />

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
			<div class="form-group">
				<div class="row">
					<div class="col-3 bg-dark text-white pt-1 ml-3">
						Сума на Ордера
					</div>
					<div class="col">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="far fa-euro-sign fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
							</div>
							<input class="form-control w-25" type="text" name="nTotalSum" id="nTotalSum" onkeyup="sumResto();" onkeypress="return formatMoney( event );" />
							<div class="input-group-append">
								<span class="far fa-fw" data-fa-transform="right-22 down-10" title="">лв.</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row my-1">
					<div class="col-3 bg-dark text-white pt-1 ml-3">
						Платена Сума
					</div>
					<div class="col">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="far fa-euro-sign fa-fw" data-fa-transform="right-22 down-10" title=""></span>
							</div>
							<input class="form-control w-25" type="text" name="nPaidSum" id="nPaidSum" onkeyup="sumResto();" onkeypress="return formatMoney( event );" />
							<div class="input-group-append">
								<span class="far fa-fw" data-fa-transform="right-22 down-10" title="">лв.</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-3 bg-dark text-white pt-1 ml-3">
						Ресто
					</div>
					<div class="col">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="far fa-euro-sign fa-fw" data-fa-transform="right-22 down-10" title=""></span>
							</div>
							<input class="form-control w-25" type="text" name="nRestSum" id="nRestSum"readonly />
							<div class="input-group-append">
								<span class="far fa-fw" data-fa-transform="right-22 down-10" title="">лв.</span>
							</div>
						</div>
					</div>
				</div>

				<div class="dropdown-divider py-1"></div>

				<div class="row">
					<div class="col-3 bg-dark text-white pt-1 ml-3">
						Документ №
					</div>
					<div class="col">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="far fa-barcode fa-fw" data-fa-transform="right-22 down-10" title=""></span>
							</div>
							<input class="form-control w-25" name="nDocNum" id="nDocNum"  type="text" onclick="openDoc();" readonly />
						</div>
					</div>
				</div>
				<div class="row my-1">
					<div class="col-3 bg-dark text-white pt-1 ml-3">
						Сметка
					</div>
					<div class="col">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="fas fa-coins fa-fw" data-fa-transform="right-22 down-10" title=""></span>
							</div>
							<select	name="nIDBankAccount" id="nIDBankAccount" class="form-control w-25" readonly ></select>
						</div>
					</div>
				</div>
				<div class="row my-1">
					<div class="col-3 bg-dark text-white pt-1 ml-3">
						Допълнителна информация
					</div>
					<div class="col">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="fas fa-info-circle fa-fw" data-fa-transform="right-22 down-10" title=""></span>
							</div>
							<textarea class="form-control w-25" name="sNote" id="sNote" rows="2"></textarea>
						</div>
					</div>
				</div>


				{if $bView}
				<div class="row px-4 pt-4">Фирми - Салда </div>
				<div class="row my-1" id="result" rpc_paging="off" rpc_excel_panel="off" rpc_resize="off" style="height: 100px; overflow: auto;"></div>
				{else}
					&nbsp;
				{/if}
			</div>
		</div>
		<div class="fixed-bottom text-center p-2">
			{if $grant_right and $status eq "active"}
				<button class="btn btn-block btn-danger py-1" onClick="annulment(); return false;"><i class="far fa-times"></i>&nbsp; Анулирай </button>
			{/if}
		</div>
	</div>
</form>

<script>
	onInit();
</script>