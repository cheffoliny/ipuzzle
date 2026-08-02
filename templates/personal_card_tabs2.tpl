{literal}
<script>
function tab_href(page) {
    var limitCardId = document.getElementById('nIDLimitCard');
    var target = document.getElementById(page);

    if (!limitCardId || !target) {
        return false;
    }

    target.href = 'page.php?page=' + page + '&id_limit_card=' + limitCardId.value;
    return true;
}
</script>
{/literal}

<ul class="nav nav-tabs ui-person-card-tabs ui-personal-card-subtabs" aria-label="Операции по лимитна карта">
    <li class="nav-item">
        {if $page eq 'personal_card_operations'}
            <a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-list" aria-hidden="true"></span><br>Операции</a>
        {else}
            <a class="nav-link" href="#" onclick="return tab_href('personal_card_operations');" id="personal_card_operations"><span class="ui-icon ui-icon-list" aria-hidden="true"></span><br>Операции</a>
        {/if}
    </li>
    <li class="nav-item">
        {if $page eq 'personal_card_ppp'}
            <a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-document" aria-hidden="true"></span><br>ППП</a>
        {else}
            <a class="nav-link" href="#" onclick="return tab_href('personal_card_ppp');" id="personal_card_ppp"><span class="ui-icon ui-icon-document" aria-hidden="true"></span><br>ППП</a>
        {/if}
    </li>
</ul>
