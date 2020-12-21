{literal}
	<script>
		function tab_href( page ) 
		{
				var oID = $('nID');
				obj = document.getElementById(page);
				obj.href = "page.php?page=" + page + "&id=" + oID.value;
				return true; 	
		}
	</script>
	<style>
	#active,
	#inactive
	{
		text-align:center !important;
 		padding: 3px 15px 3px 15px;
	}
	
	#tabs td
	{
		white-space:nowrap !important;
	}
	</style>
{/literal}

<div id="search" style="width: 100%;">

	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="tabs">
		
		<tr>
			<td width="1" class="inactive"></td>
			{if $page eq sale_doc_info}
				<td id="active" style="width:150px;" nowrap="nowrap">
					Информация
				</td>
			{else}
				<td id="inactive" style="width:150px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('sale_doc_info');" id="sale_doc_info">Информация</a>
				</td>
			{/if}
			 <td width="1" id="passive"></td>
			{if $page eq sale_doc_orders}
				<td id="active" style="width:150px;" nowrap="nowrap">
					Ордери
				</td>
			{else}
				<td id="inactive" style="width:150px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('sale_doc_orders');" id="sale_doc_orders">Ордери</a>
				</td>
			{/if}
				<td width="1" id="passive"></td>
			{if $page eq sale_doc_inventory}
				<td id="active" style="width:150px;" nowrap="nowrap">
					Опис
				</td>
			{else}
				<td id="inactive" style="width:150px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('sale_doc_inventory')" id="sale_doc_inventory">Опис</a>
				</td>
			{/if}
			<td style="width:70%;" id="passive"></td>

          <td id="lpassive"></td>
		</tr>
	
	</table>

</div>