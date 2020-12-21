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
	
	</script>
{/literal}


<form name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />

    {include file='client_tabs.tpl'}
				
    <div id="result" rpc_resize="off"></div>

</form>

<script>
	onInit();
</script>