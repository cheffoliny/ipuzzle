<script>
	rpc_debug=true;
	{literal}
	
		function viewAsset(id)
		{
			
			dialogAssetInfo( id );
			
		}
	{/literal}
	
</script>

	
	<form id="form1" name="form1">
	<div class="page_caption">Подчинени активи на актив №{$nID} </div>
	<input type="hidden"  name="nID" id="nID" value="{$nID}">
	  	<table  cellspacing="0" cellpadding="0" width="100%"  border="0" id="filter" >
  		<tr>
  			<td>
  				{include file=asset_info_tabs.tpl}
  				<br>
  			</td>
  		</tr>
  	</table>
		<div id="result" >
		</div>
	</form>

<script>
	loadXMLDoc2('result')
</script>