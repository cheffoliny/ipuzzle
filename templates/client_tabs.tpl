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

<div class="row navbar-dark bg-faded ui-client-header">
    <div class="col-sm-12 col-lg-12">
        <p id="head_window" class="text-white text-truncate text-uppercase pt-2"><span class="ui-icon ui-icon-tag" aria-hidden="true"></span> {$client}</p>
    </div>
</div>

<ul class="nav nav-tabs navbar-dark bg-faded mb-1 ui-client-tabs">

    {if $page eq 'client_info'}
        <li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-info" aria-hidden="true"></span><span class="sr-only">Информация</span></a></li>
    {else}
        <li class="nav-item text-center" title="Информация"><a class="nav-link" href="#" onclick="return tab_href( 'client_info' );" id="client_info"><span class="ui-icon ui-icon-info" aria-hidden="true"></span><span class="sr-only">Информация</span></a></li>
    {/if}

    {if $page eq 'client_objects'}
        <li class="nav-item text-center" title="Обекти"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-home" aria-hidden="true"></span><span class="sr-only">Обекти</span></a></li>
    {else}
        <li class="nav-item text-center" title="Обекти"><a class="nav-link" href="#" onclick="return tab_href( 'client_objects' );" id="client_objects"><span class="ui-icon ui-icon-home" aria-hidden="true"></span><span class="sr-only">Обекти</span></a></li>
    {/if}

    {if $page eq 'client_payments'}
        <li class="nav-item text-center" title="Плащания"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-card" aria-hidden="true"></span><span class="sr-only">Плащания</span></a></li>
    {else}
        <li class="nav-item text-center" title="Плащания"><a class="nav-link" href="#" onclick="return tab_href( 'client_payments' );" id="client_payments"><span class="ui-icon ui-icon-card" aria-hidden="true"></span><span class="sr-only">Плащания</span></a></li>
    {/if}

</ul>
