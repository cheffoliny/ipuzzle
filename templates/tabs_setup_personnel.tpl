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

    {if $page eq "admin_personnels"}
        <li class="nav-item text-center" title="Служители">
            <a class="nav-link active" href="#">Служители</a>
        </li>
    {else}
        <li class="nav-item text-center" title="Служители">
            <a class="nav-link" href="#" onclick="return tab_href('admin_personnels');" id='admin_personnel'>Служители</a>
        </li>
    {/if}

    {if $page eq "person_schedule"}
        <li class="nav-item text-center" title="График"><a class="nav-link active" href="#">График</a></li>
    {else}
        <li class="nav-item text-center" title="График">
            <a class="nav-link" href="#" onclick="return tab_href('person_schedule');" id='setup_clients'>График</a>
        </li>
    {/if}

</ul>
