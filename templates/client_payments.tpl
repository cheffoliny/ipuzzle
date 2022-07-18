{literal}
	<script>
		rpc_debug = true;
		rpc_method='POST';
			
		function onInit()
		{
			loadXMLDoc2( 'result' );
		}
		
		function openSaleDoc( id )
		{
			//dialogSaleDocInfo2( id );
			dialogSale2( id );
		}

		/* За нови и стари фактури */
		function printSaleDoc( id, ver ) {
			vPopUp({
				url: `api/api_print_invoice.php?id=${id}&v=${ver}`,
				name: `printPdf${id}`,
				width: 860,
				height: 650,
				reload: true,
			});
		}

		function signInvoice(id) {
			$('nIDInvoice').value = id;
			var send = confirm('Наистина ли искате да изпратите фактура?');
			if(send === true) {
//				var res = loadDirect('signPDF', 'L');
				jQuery('body').addClass("loading");
				jQuery.ajax({
					type: "POST",
					url: 'api/api_general.php?action_script=api/api_client_payments.php&api_action=signPDF&rpc_version=2',
					data: {
						nID : jQuery('#nID').val(),
						nIDInvoice : id,
						sfield : 'doc_date',
						stype : 1

					},
					success: function(data) {

//                        console.log(data);

						if(data !== undefined && data >= "1") {
							alert('Фактурата е изпратена успешно!');
						} else {
							alert('Грешка при изпращане на фактура!');
						}
						jQuery('body').removeClass("loading");
					}
				})
			}
		}
	</script>
{/literal}


<form name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="nIDInvoice" name="nIDInvoice" value="0" />

    {include file='client_tabs.tpl'}
				
    <div id="result" rpc_resize="off"></div>

</form>

<script>
	onInit();
</script>