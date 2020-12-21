{literal}
	<script>
	
		rpc_debug = true;
		rpc_excel_panel = "off";

		
			
		function viewRegister( id )
		{
			var arr = new Array();
			arr = id.split(',');
			
			if( arr[0] !== 'undefined' && arr[0] != '' && arr[0] > 0) 
			{
				dialogReceipt( arr[0] );
			}			
		}

		function viewObject( id )
		{
			var arr = new Array();
			arr = id.split(',');
	
			if( arr[1] !== 'undefined' && arr[1] != '' && arr[1] > 0) 
			{
				var params = 'nID='+arr[1];	
				dialogObjectInfo(params, 'object');
			}

		}
 
		function deleteRegister( id )
		{
			var arr = new Array();
			arr = id.split(',');
						
			if( arr[0] !== 'undefined' && arr[0] != '' && arr[0] > 0) 
			{
				if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
				{
					$( 'nID' ).value =arr[0];
					loadXMLDoc2( 'delete', 1 );
				}				
			}
		}
		
	</script>
{/literal}



<form id="form1" name="form1" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0">
	<input type="hidden" id="nIDService" name="nIDService" value="{$nIDService}">
	<input type="hidden" id="nIDFirm" name="nIDFirm" value="{$nIDFirm}">
	
	
	<table class="input">

		<tr>
			<td class="page_name">
				Регистър
			</td>
			
			<td align="right" class="buttons">
				<button id="b70" name="Button" onClick="dialogReceipt( 0 )" ><img src="images/plus.gif"> Добави </button>
			</td>			
		</tr>
	</table>

<hr/>
		
	<div id="result"></div>
</form>

{literal}
<script>
loadXMLDoc2( 'result' );
</script>
{/literal}