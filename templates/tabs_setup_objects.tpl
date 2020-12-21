{literal}
<script>

    function tab_href( page )
    {
        $( page ).href = "page.php?page="+ page + "";
        return true;
    };

</script>
{/literal}

<ul class="nav nav-tabs nav-intelli">

    {if $page eq "setup_objects"}
        <li class="nav-item text-center" title="Обекти">
            <a class="nav-link active" href="#">Обекти</a>
        </li>
    {else}
        <li class="nav-item text-center" title="Обекти">
            <a class="nav-link" href="#" onclick="return tab_href('setup_objects');" id='setup_objects'>Обекти</a>
        </li>
    {/if}

    {if $page eq "setup_clients"}
        <li class="nav-item text-center" title="Контрагенти"><a class="nav-link active" href="#">Контрагенти</a></li>
    {else}
        <li class="nav-item text-center" title="Контрагенти">
            <a class="nav-link" href="#" onclick="return tab_href('setup_clients');" id='setup_clients'>Контрагенти</a>
        </li>
    {/if}

</ul>
