{literal}
<script>

    function tab_href( page )
    {
        $( page ).href = "page.php?page="+ page + "";
        return true;
    };

</script>
{/literal}

<ul class="nav nav-tabs nav-intelli ui-personnel-section-tabs">

    {if $page eq "admin_personnels"}
        <li class="nav-item text-center" title="Служители">
            <a class="nav-link active" href="#"><span class="ui-icon ui-icon-users" aria-hidden="true"></span> Служители</a>
        </li>
    {else}
        <li class="nav-item text-center" title="Служители">
            <a class="nav-link" href="#" onclick="return tab_href('admin_personnels');" id='admin_personnels'><span class="ui-icon ui-icon-users" aria-hidden="true"></span> Служители</a>
        </li>
    {/if}

    {if $page eq "person_schedule"}
        <li class="nav-item text-center" title="График"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span> График</a></li>
    {else}
        <li class="nav-item text-center" title="График">
            <a class="nav-link" href="#" onclick="return tab_href('person_schedule');" id='person_schedule'><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span> График</a>
        </li>
    {/if}


    {if $page eq "admin_salary_total"}
        <li class="nav-item text-center" title="Заплати (Обобщена)"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-money" aria-hidden="true"></span> [OБ] Заплати</a></li>
    {else}
        <li class="nav-item text-center" title="Заплати (Обобщена)">
            <a class="nav-link" href="#" onclick="return tab_href('admin_salary_total');" id='admin_salary_total'><span class="ui-icon ui-icon-money" aria-hidden="true"></span> [OБ] Заплати</a>
        </li>
    {/if}

    {if $page eq "admin_salary"}
        <li class="nav-item text-center" title="Заплати (Подробна)"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-list" aria-hidden="true"></span> [Подр] Заплати</a></li>
    {else}
        <li class="nav-item text-center" title="Заплати (Обобщена)">
            <a class="nav-link" href="#" onclick="return tab_href('admin_salary');" id='admin_salary'><span class="ui-icon ui-icon-list" aria-hidden="true"></span> [Подр] Заплати</a>
        </li>
    {/if}
</ul>
