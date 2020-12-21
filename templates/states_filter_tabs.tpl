{literal}
	<script>
		function tab_href( page ) 
		{
				var oID = $('nID');
				if(oID.value==0)
				{
					alert("Филтърът още не е запазен!");
					return false;
				}
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

<div id="search">

	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="tabs">
		
		<tr>
			<td width="1" class="inactive"></td>
			{if $page eq states_filter}
				<td id="active" style="width:100px;" nowrap="nowrap">
					Информация
				</td>
			{else}
				<td id="inactive" style="width:100px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('states_filter');" id="states_filter">Информация</a>
				</td>
			{/if}
			 <td width="1" id="passive"></td>
			{if $page eq states_filter_fields}
				<td id="active" style="width:100px;" nowrap="nowrap">
					Видими&nbsp;полета
				</td>
			{else}
				<td id="inactive" style="width:100px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('states_filter_fields');" id="states_filter_fields">Видими&nbsp;полета</a>
				</td>
			{/if}
				<td width="1" id="passive"></td>
			{if $page eq states_filter_totals}
				<td id="active" style="width:100px;" nowrap="nowrap">
					Автоматичен
				</td>
			{else}
				<td id="inactive" style="width:100px;" nowrap="nowrap">
					<a href="#" onclick="return tab_href('states_filter_totals')" id="states_filter_totals">Автоматичен</a>
				</td>
			{/if}
			<td style="width:70%;" id="passive"></td>

          <td id="lpassive"></td>
		</tr>
	
	</table>

</div>