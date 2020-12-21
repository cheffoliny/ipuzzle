{literal}
	<script>

	function tab_href( page ) {
		var oID = $('nID');
		if( oID.value == 0 ) {
			alert( "Информацията за Ордера не е запазена!" );
			return false;
		}
		obj = document.getElementById( page );
		obj.href = "page.php?page=" + page + "&id=" + oID.value;

		return true;
	}
	
	</script>
	

{/literal}

<ul class="nav nav-tabs navbar-dark bg-faded mb-1" id="search">


		{if $page eq order_info}
			<li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#"><i class="fa fa-info fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Информация &nbsp;&nbsp;</a></li>
		{else}
			<li class="nav-item text-center" title="Информация"><a class="nav-link" href="#" onclick="return tab_href('order_info');" id='order_info'><i class="fa fa-info fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Информация &nbsp;&nbsp;</a></li>
		{/if}



		{if $page eq order_inventory}
			<li class="nav-item text-center" title="Опис"><a class="nav-link active" href="#"><i class="fa fa-list-alt fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Опис &nbsp;&nbsp;</a></li>
		{else}
			<li class="nav-item text-center" title="Опис"><a class="nav-link" href="#" onclick="return tab_href('order_inventory');" id='order_inventory'><i class="fa fa-list-alt fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Опис &nbsp;&nbsp;</a></li>
		{/if}

</ul>