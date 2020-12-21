<script>
	rpc_debug=true;
	
	{literal}
		function viewPPP(id)
		{
			dialogAssetsPPP(id);
		}
	{/literal}
</script>
<form id="form1" name="form1" onsubmit="return false">

	<input type="hidden" id="nID" name="nID" value="{$nID}" />
	
	<div class="page_caption">ППП за актив №{$nID} </div>
	
	 <table  cellspacing="0" cellpadding="0" width="100%"  border="0" id="filter" >
  		<tr>
  			<td>
  				{include file=asset_info_tabs.tpl}
  				<br>
  			</td>
  		</tr>
  	</table>
	
	<div id="result">
	
	</div>
	
</form>
<script>
	loadXMLDoc2('result');
</script>