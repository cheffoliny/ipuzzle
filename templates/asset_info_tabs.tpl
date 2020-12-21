{literal}
	<script>
		function tab_href( page ) 
		{
				var oID = $('nID');
				if(oID.value==0)
				{
					alert("Активът още не е въведен!");
					return false;
				}
				obj = document.getElementById(page);
				obj.href = "page.php?page=" + page + "&nID=" + oID.value;
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

<div id="search">

	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="tabs">
		
		<tr>
			<td width="1" class="inactive"></td>
			{if $page eq asset_info}
				<td id="active" style="width:150px;" nowrap="nowrap">
					Информация
				</td>
			{else}
				<td id="inactive" style="width:150px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('asset_info');" id="asset_info">Информация</a>
				</td>
			{/if}
			 <td width="1" id="passive"></td>
			{if $page eq asset_info_ppp}
				<td id="active" style="width:150px;" nowrap="nowrap">
					ППП
				</td>
			{else}
				<td id="inactive" style="width:150px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('asset_info_ppp');" id="asset_info_ppp">ППП</a>
				</td>
			{/if}
				<td width="1" id="passive"></td>
			{if $page eq asset_info_sub_assets}
				<td id="active" style="width:150px;" nowrap="nowrap">
					Подчинени активи
				</td>
			{else}
				<td id="inactive" style="width:150px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('asset_info_sub_assets')" id="asset_info_sub_assets">Подчинени активи</a>
				</td>
			{/if}
			<td style="width:70%;" id="passive"></td>

          <td id="lpassive"></td>
		</tr>
	
	</table>

</div>