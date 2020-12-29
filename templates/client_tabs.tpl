{literal}
	<script>
	
	function tab_href( page )
	{
		var oID = $('nID');

		if( oID.value == 0 ) {
			alert( "Информацията за Клиента не е запазена!" );
			return false;
		}
		
		obj = document.getElementById( page );
		obj.href = "page.php?page=" + page + "&id=" + oID.value;
		return true;
	}
	
	</script>
{/literal}

<div class="row navbar-dark bg-faded">
    <div class="col-sm-12 col-lg-12">
        <p id="head_window" class="text-white text-truncate text-uppercase pt-2"><i class="fas fa-tag mx-4 "></i> {$client}</p>
    </div>
</div>

<ul class="nav nav-tabs navbar-dark bg-faded mb-1">

    {if $page eq client_info}
        <li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-info fa-2x"></i>&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    {else}
        <li class="nav-item text-center" title="Информация"><a class="nav-link" href="#" onclick="return tab_href( 'client_info' );" id="client_info">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-info fa-2x"></i>&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    {/if}

    {if $page eq client_objects}
        <li class="nav-item text-center" title="Обекти"><a class="nav-link active" href="#">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-home fa-2x"></i>&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    {else}
        <li class="nav-item text-center" title="Обекти"><a class="nav-link" href="#" onclick="return tab_href( 'client_objects' );" id="client_objects">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-home fa-2x"></i>&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    {/if}

    {if $page eq client_payments}
        <li class="nav-item text-center" title="Плащания"><a class="nav-link active" href="#">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-credit-card fa-2x"></i>&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    {else}
        <li class="nav-item text-center" title="Плащания"><a class="nav-link" href="#" onclick="return tab_href( 'client_payments' );" id="client_payments">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-credit-card fa-2x"></i>&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    {/if}

</ul>