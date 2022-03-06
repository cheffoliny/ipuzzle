{literal}
<script>

    function tab_href( page ) {
        $( page ).href = "page.php?page="+ page + "";
        return true;
    };

</script>
{/literal}

<ul class="nav nav-tabs nav-intelli">

    {if $page eq "setup_ppp"}
        <li class="nav-item text-center" title="Стокови разписки">
            <a class="nav-link active" href="#">Стокови разписки</a>
        </li>
    {else}
        <li class="nav-item text-center" title="Стокови разписки">
            <a class="nav-link" href="#" onclick="return tab_href('setup_ppp');" id='setup_ppp'>Стокови разписки</a>
        </li>
    {/if}

    {if $page eq "view_states2"}
        <li class="nav-item text-center" title="Наличности"><a class="nav-link active" href="#">Наличности</a></li>
    {else}
        <li class="nav-item text-center" title="Наличности">
            <a class="nav-link" href="#" onclick="return tab_href('view_states2');" id='view_states2'>Наличности</a>
        </li>
    {/if}

</ul>