{literal}
	<script>
	
		function tab_href( sPageID )
		{
			if( sPageID == 'set_storagehouses_mols' )
			{
				if( $('nID').value != 0 )
				{
					$( sPageID ).href = "page.php?page="+ sPageID + "&id=" + $('nID').value;
					return true;
				}
				else
				{
					alert( 'Информацията по склада не е запазена!' );
					return false;
				}
			}
			else
			{
				$( sPageID ).href = "page.php?page="+ sPageID + "&id=" + $('nID').value;
				return true;
			}
		};
	
	</script>
	
	<style>
		#active,
		#inactive
		{
			width:100px;
		}
	</style>
	
{/literal}
<div id="search">
	<table cellspacing="0" cellpadding="0" width="100%" id="tabs">
    	<tr>
			<td width="1" id="passive"></td>
				
				{* Информация *}
				{if $page eq set_storagehouses}
					<td id="active" style="width:100px;">Информация</td>
				{else}
					<td id="inactive" style="width:100px;">
						<a href="#" onclick="return tab_href( 'set_storagehouses' );" id="set_storagehouses">Информация</a>
					</td>
				{/if}
				
				<td width="1" id="passive"></td>
				
				{* Материално-отговорни Лица *}
				{if $page eq set_storagehouses_mols}
					<td id="active" style="width:180px;">Материално-отговорни Лица</td>
				{else}
					<td id="inactive" style="width:180px;">
						<a href="#" onclick="return tab_href( 'set_storagehouses_mols' );" id="set_storagehouses_mols">Материално-отговорни Лица</a>
					</td>
				{/if}
				
				<td width="1" id="passive"></td>
				
			<td id="lpassive">&nbsp;</td>
		</tr>
	</table>
</div>