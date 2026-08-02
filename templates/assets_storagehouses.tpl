{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		function editStorageHouse(id)
		{
			dialogAssetStoragehouses(id);
		}
		
		function delAssetsStoragehouse(id)
		{
			if ( confirm('Наистина ли желаете да премахнете зaписа?') )
			{
				$('nID').value = id;
				loadXMLDoc2('delete', 1);
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-assets-list" onSubmit="return false;">
<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Активи - СКЛАДОВЕ</td>
			<td class="buttons">
				<button type="button" onclick="editStorageHouse(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		loadXMLDoc2('result');
	</script>
{/literal}
