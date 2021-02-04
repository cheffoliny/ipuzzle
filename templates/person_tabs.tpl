{literal}
<script>
function tab_href (page) {
	if (document.getElementById('id').value<=0) {
		alert ("Служитела все още не съществува в системата!");
		return false;
	}
	obj = document.getElementById(page);
	obj.href = "page.php?page="+page+"&id="+document.getElementById('id').value+"&enable_refresh="+document.getElementById('nEnableRefresh').value;
	return true;
};
</script>
{/literal}
<div class="row navbar-dark bg-faded py-1">
    <div class="col-sm-8 col-lg-8">
        <h5>
            {if $id}
                <span id="head_window" class="text-warning text-truncate text-uppercase ml-4" > {$person_name}</span>
            {else}
                <span id="head_window" class="text-white text-truncate text-uppercase ml-4"> {$person_name}</span>
            {/if}
        </h5>
    </div>
    <div class="col-sm-4 col-lg-4">


    </div>
</div>
<ul class="nav nav-tabs navbar-dark bg-faded mb-1">

    {if $page eq personInfo}
        <li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#">&nbsp; Инфо &nbsp;&nbsp;</a></li>
    {else}
        <li class="nav-item text-center" title="Информация">{if $tabs.info}<a class="nav-link" href="#" onclick="return tab_href('personInfo');" id='personInfo'> Инфо </a>{else}<a class="nav-link disabled" href="#"> Инфо </a>{/if}</li>
    {/if}

    {* Служебни данни *}
    {if $page eq person_data}
        <li class="nav-item text-center" title="Служебна информация"><a class="nav-link active" href="#">&nbsp; Служебни </a></li>
    {else}
        <li class="nav-item text-center" title="Служебна информация">{if $tabs.data}<a class="nav-link" href="#" onclick="return tab_href('person_data');" id='person_data'>&nbsp;&nbsp; Служебни </a>{else}<a class="nav-link disabled" href="#">&nbsp;&nbsp; Служебни</a>{/if}</li>
    {/if}

    {* Документи *}
    {if $page eq person_docs}
        <li class="nav-item text-center" title="Документи"><a class="nav-link active" href="#">&nbsp; Документи </a></li>
    {else}
        <li class="nav-item text-center" title="Документи">{if $tabs.docs}<a class="nav-link" href="#" onclick="return tab_href('person_docs');" id='person_docs'>&nbsp;&nbsp; Документи </a>{else}<a class="nav-link disabled" href="#">&nbsp;&nbsp; Документи</a>{/if}</li>
    {/if}

    {* Трудов договор *}
    {if $page eq person_contract}
        <li class="nav-item text-center" title="Трудов договор"><a class="nav-link active" href="#"> Договор </a></li>
    {else}
        <li class="nav-item text-center" title="Договор">
            {if $tabs.contr}<a class="nav-link" href="#" onclick="return tab_href('person_contract');" id='person_contract'>&nbsp;&nbsp;Договор </a>
            {else}<a class="nav-link disabled" href="#">&nbsp;&nbsp; Договор</a>{/if}
        </li>
    {/if}

    {* Отпуск *}
    {if $page eq person_leave}
        <li class="nav-item text-center" title="Отпуск"><a class="nav-link active" href="#">&nbsp; Отпуск </a></li>
    {else}
        <li class="nav-item text-center" title="Отпуск">
            {if $tabs.leave}<a class="nav-link" href="#" onclick="return tab_href('person_leave');" id='person_leave'>&nbsp;&nbsp; Отпуск </a>
            {else}<a class="nav-link disabled" href="#">&nbsp;&nbsp;Отпуск</a>{/if}
        </li>
    {/if}

    {* Работна заплата *}
    {if $page eq person_salary}
        <li class="nav-item text-center" title="Заплата"><a class="nav-link active" href="#">&nbsp; Заплата </a></li>
    {else}
        <li class="nav-item text-center" title="Заплата">
            {if $tabs.salary}<a class="nav-link" href="#" onclick="return tab_href('person_salary');" id='person_salary'>&nbsp;&nbsp; Заплата </a>
            {else}<a class="nav-link disabled" href="#">&nbsp;&nbsp;Заплата</a>{/if}
        </li>
    {/if}
</ul>