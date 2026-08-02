{literal}
<script>
function tab_href(page) {
    var personId = document.getElementById('nID');
    var limitCardId = document.getElementById('nIDLimitCard');
    var target = document.getElementById(page);

    if (!personId || !limitCardId || !target) {
        return false;
    }

    target.href = 'page.php?page=' + page + '&nID=' + personId.value + '&id_limit_card=' + limitCardId.value;
    return true;
}
</script>
{/literal}

<ul class="nav nav-tabs ui-person-card-tabs ui-personal-card-tabs" aria-label="Личен картон">
    <li class="nav-item">
        {if $page eq 'personal_card'}
            <a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-card" aria-hidden="true"></span><br>Лимитни карти</a>
        {else}
            <a class="nav-link" href="#" onclick="return tab_href('personal_card');" id="personal_card"><span class="ui-icon ui-icon-card" aria-hidden="true"></span><br>Лимитни карти</a>
        {/if}
    </li>
    <li class="nav-item">
        {if $page eq 'personal_card_salary'}
            <a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-money" aria-hidden="true"></span><br>Заплата</a>
        {else}
            <a class="nav-link" href="#" onclick="return tab_href('personal_card_salary');" id="personal_card_salary"><span class="ui-icon ui-icon-money" aria-hidden="true"></span><br>Заплата</a>
        {/if}
    </li>
</ul>
