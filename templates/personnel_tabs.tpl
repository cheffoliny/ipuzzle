{literal}
<script>

function tab_href( page ) {
	var oID = $('nID');	
	if ( oID && parseInt( oID.value ) ) {
		obj = document.getElementById(page);
		obj.href = "page.php?page=" + page + "&nID=" + oID.value {/literal} {literal};
		return true; 	
	}	
}

</script>
{/literal}

<div class="row navbar-dark bg-faded ui-person-card-header">
    <div class="col-sm-8 col-lg-8">
        <h5>
            <span id="head_window" class="text-white text-truncate text-uppercase ml-4"><span class="ui-icon ui-icon-user" aria-hidden="true"></span> {$object}</span>
        </h5>
    </div>
    <div class="col-sm-4 col-lg-4">


    </div>
</div>

<ul class="nav nav-tabs navbar-dark bg-faded mb-1 ui-person-card-tabs">

		{foreach key=key item=item from=$view name=rights}

			{if $key == 'personInfo_view'}
				{if $page eq 'personInfo'}
                    <li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-info" aria-hidden="true"></span><br/>Инфо</a></li>
				{else}
					<li class="nav-item text-center" title="Информация">{if $view.personInfo_view}<a class="nav-link" href="#" onclick="return tab_href('personInfo');" id='personInfo'><span class="ui-icon ui-icon-info" aria-hidden="true"></span><br/>Инфо</a>{else}<a class="nav-link disabled" href="#"><span class="ui-icon ui-icon-info" aria-hidden="true"></span><br/>Инфо</a>{/if}</li>
				{/if}
			{/if}

			{if $key == 'object_contract_view'}
                {if $page eq 'object_contract'}
                    <li class="nav-item text-center" title="Договор..."><a class="nav-link active"><span class="ui-icon ui-icon-certificate" aria-hidden="true"></span><br />Договор</a></li>
				{else}
                    <li class="nav-item text-center" title="Договор..." >{if $view.object_contract_view}<a class="nav-link" href="#" onclick="return tab_href('object_contract');" id='object_contract'><span class="ui-icon ui-icon-certificate" aria-hidden="true"></span><br />Договор</a>{else}<a class="nav-link disabled"><span class="ui-icon ui-icon-certificate" aria-hidden="true"></span><br />Договор</a>{/if}</li>
				{/if}

			{/if}

			{if $key == 'object_taxes_view'}
                {*Такси 	  *}
                {if $page eq 'object_taxes'}
                    <li class="nav-item text-center" title="Такси"><a class="nav-link active"><span class="ui-icon ui-icon-money" aria-hidden="true"></span><br/>Такси</a></li>
				{else}
                    <li class="nav-item text-center" title="Такси" >{if $view.object_taxes_view}<a class="nav-link" href="#" onclick="return tab_href('object_taxes');" id='object_taxes'><span class="ui-icon ui-icon-money" aria-hidden="true"></span><br/>Такси</a>{else}<a class="nav-link disabled" href="#"><span class="ui-icon ui-icon-money" aria-hidden="true"></span><br/>Такси</a>{/if}</li>
				{/if}

			{/if}

            {if $isSOD &&  $key == 'object_messages_view'}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">

                        {if $page eq 'object_archiv'}<span class="ui-icon ui-icon-archive" aria-hidden="true"></span><br/>Събития
                        {elseif $page eq 'object_messages'} <span class="ui-icon ui-icon-signal" aria-hidden="true"></span><br/>Сигнали
                        {elseif $page eq 'object_sectors'} <span class="ui-icon ui-icon-cube" aria-hidden="true"></span><br/>Сектори
                        {elseif $page eq 'object_zones'} <span class="ui-icon ui-icon-cubes" aria-hidden="true"></span><br/>Зони
                        {elseif $page eq 'object_users'} <span class="ui-icon ui-icon-user" aria-hidden="true"></span><br/>Потребители
                        {else} <span class="ui-icon ui-icon-monitor" aria-hidden="true"></span><br/>СОД
                        {/if}
                    </a>
                    <div class="dropdown-menu">

                        {if $view.object_messages_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_messages');" id='object_messages'>
                                <span class="ui-icon ui-icon-signal" aria-hidden="true"></span> Сигнали
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_sectors');" id='object_sectors'>
                                <span class="ui-icon ui-icon-cube" aria-hidden="true"></span> Сектори
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_zones');" id='object_zones'>
                                <span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> Зони
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_users');" id='object_users'>
                                <span class="ui-icon ui-icon-user" aria-hidden="true"></span> Потребители</a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-signal" aria-hidden="true"></span> Сигнали</a>
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-cube" aria-hidden="true"></span> Сектори</a>
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> Зони</a>
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-user" aria-hidden="true"></span> Потребители</a>
                        {/if}


                    {*{if $key == 'object_archiv_view'}*}
                        {*Архив*}
                        {if $view.object_archiv_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_archiv');" id='object_archiv'><span class="ui-icon ui-icon-archive" aria-hidden="true"></span> Събития</a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-archive" aria-hidden="true"></span> Събития</a>
                        {/if}
                    {*{/if}*}
                    </div>
                </li>
			{/if}
		{/foreach}

        <li class="nav-item dropdown ml-auto mr-5">
            {if $isService}
                <a class="nav-link dropdown-toggle text-warning text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="Действия"><span class="ui-icon ui-icon-settings" aria-hidden="true"></span></a>
            {else}
                <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="Действия"><span class="ui-icon ui-icon-settings" aria-hidden="true"></span></a>
            {/if}
            <div class="dropdown-menu">
                {if $bEditStatuses}
                    <a class="dropdown-item text-puzzle" onClick="ServiceStatus();"><span class="ui-icon ui-icon-eye" aria-hidden="true"></span> Байпас</a>
                {/if}
                <a class="dropdown-item text-puzzle" href="#" onClick="makePayment();"><span class="ui-icon ui-icon-card" aria-hidden="true"></span> Плащане</a>
                <a class="dropdown-item text-puzzle" href="#" onClick="techSupport();"><span class="ui-icon ui-icon-wrench" aria-hidden="true"></span> Задача</a>
            </div>
        </li>
</ul>

