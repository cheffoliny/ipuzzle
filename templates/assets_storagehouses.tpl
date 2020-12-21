{literal}
	<script>
		rpc_debug = true;
		
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

<form action="" name="form1" id="form1" onSubmit="return false;">
<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Активи - СКЛАДОВЕ</td>
			<td class="buttons">
				<button onclick="editStorageHouse(0);"><img src="images/plus.gif"> Добави </button>
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