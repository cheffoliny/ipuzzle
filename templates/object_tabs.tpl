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

function TechRequestForObject() {
    var oID = $('nID');
    dialogTechRequestForObject(0, oID.value);
}

</script>
{/literal}

<div class="row navbar-dark bg-faded ui-object-header">
    <div class="col-sm-8 col-lg-8">
        <h5>
        {if $isService}
            <span id="head_window" class="text-warning text-truncate text-uppercase ml-4"><span class="ui-icon ui-icon-home" aria-hidden="true"></span> {$object}</span>
        {else}
            <span id="head_window" class="text-white text-truncate text-uppercase ml-4"><span class="ui-icon ui-icon-home" aria-hidden="true"></span> {$object}</span>
        {/if}
        </h5>
    </div>
    <div class="col-sm-4 col-lg-4">


    </div>
</div>

<ul class="nav nav-tabs navbar-dark bg-faded mb-1 ui-object-tabs">

		{foreach key=key item=item from=$view name=rights}

			{if $key == 'object_info_view'}
				{if $page eq 'object_info'}
                    <li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#"><span class="ui-icon ui-icon-info ui-object-tab-icon" aria-hidden="true"></span><br/>&nbsp;&nbsp; Инфо &nbsp;&nbsp;</a></li>
				{else}
					<li class="nav-item text-center" title="Информация">{if $view.object_info_view}<a class="nav-link" href="#" onclick="return tab_href('object_info');" id='object_info'><span class="ui-icon ui-icon-info ui-object-tab-icon" aria-hidden="true"></span><br/>&nbsp;&nbsp; Инфо &nbsp;&nbsp;</a>{else}<a class="nav-link disabled" href="#"><span class="ui-icon ui-icon-info ui-object-tab-icon" aria-hidden="true"></span><br/>&nbsp;&nbsp; Инфо &nbsp;&nbsp;</a>{/if}</li>
				{/if}
			{/if}


            {if $isFO and  $key == 'object_shifts_view' }
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">

                        {if $page eq 'object_shifts'} <span class="ui-icon ui-icon-tags ui-object-tab-icon" aria-hidden="true"></span><br/> Тип смени &nbsp;&nbsp;
                        {elseif $page eq 'object_duty'} <span class="ui-icon ui-icon-refresh ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp; Смяна&nbsp;&nbsp;
                        {elseif $page eq 'object_personnel_schedule'} <span class="ui-icon ui-icon-user ui-object-tab-icon" aria-hidden="true"></span><br/> В график
                        {else} <span class="ui-icon ui-icon-calendar ui-object-tab-icon" aria-hidden="true"></span><br/> ГРАФИК
                        {/if}
                    </a>
                    <div class="dropdown-menu">
                        {if $view.object_shifts_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_shifts');" id='object_shifts'><span class="ui-icon ui-icon-tags" aria-hidden="true"></span> Тип смени </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-tags" aria-hidden="true"></span> Тип смени </a>
                        {/if}
                        {if $view.object_personnel_schedule_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_personnel_schedule');" id='object_personnel_schedule'><span class="ui-icon ui-icon-users" aria-hidden="true"></span> В график </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-users" aria-hidden="true"></span> В график </a>
                        {/if}
                        {if $view.object_duty_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_duty');" id='object_duty'><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span> Смяна </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span> Смяна </a>
                        {/if}
                    </div>
                </li>
            {/if}
			{*{if $key == 'object_personnel_schedule_view'}*}
				{* Служители - График *}{*	  *}

				{**}
			{*{/if}*}
			  {**}
			{*{if $key == 'object_shifts_view' and $isFO}*}
				{* Видове смени *}{*	  *}

				{**}
			{*{/if} *}

			{if $key == 'object_duty_view' and $isFO}


			{/if}

			{*{if $key == 'object_personnel_view' and $isFO}*}
				{* Служители *}{*	  *}
				{*{if $page eq object_personnel}*}
				{*{else}*}
				{*{/if}*}
					{**}
			{*{/if} *}

			{if $key == 'object_contract_view'}
                {if $page eq 'object_contract'}
                    <li class="nav-item text-center" title="Договор..."><a class="nav-link active"><span class="ui-icon ui-icon-contract ui-object-tab-icon" aria-hidden="true"></span><br />Договор</a></li>
				{else}
                    <li class="nav-item text-center" title="Договор..." >{if $view.object_contract_view}<a class="nav-link" href="#" onclick="return tab_href('object_contract');" id='object_contract'><span class="ui-icon ui-icon-contract ui-object-tab-icon" aria-hidden="true"></span><br />Договор</a>{else}<a class="nav-link disabled"><span class="ui-icon ui-icon-contract ui-object-tab-icon" aria-hidden="true"></span><br />Договор</a>{/if}</li>
				{/if}

			{/if}

			{if $key == 'object_taxes_view'}
                {*Такси 	  *}
                {if $page eq 'object_taxes'}
                    <li class="nav-item text-center" title="Такси"><a class="nav-link active"><span class="ui-icon ui-icon-money ui-object-tab-icon" aria-hidden="true"></span><br/>&nbsp; Такси &nbsp;</a></li>
				{else}
                    <li class="nav-item text-center" title="Такси" >{if $view.object_taxes_view}<a class="nav-link" href="#" onclick="return tab_href('object_taxes');" id='object_taxes'><span class="ui-icon ui-icon-money ui-object-tab-icon" aria-hidden="true"></span><br/>&nbsp; Такси &nbsp;</a>{else}<a class="nav-link disabled" href="#"><span class="ui-icon ui-icon-money ui-object-tab-icon" aria-hidden="true"></span><br/>&nbsp; Такси &nbsp;</a>{/if}</li>
				{/if}

			{/if}

            {if $isSOD &&  $key == 'object_messages_view'}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">

                        {if $page eq 'object_archiv'}<span class="ui-icon ui-icon-archive ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp;&nbsp; Събития &nbsp;&nbsp;&nbsp;
                        {elseif $page eq 'object_messages'} <span class="ui-icon ui-icon-signal ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp; Сигнали &nbsp;&nbsp;
                        {elseif $page eq 'object_sectors'} <span class="ui-icon ui-icon-cube ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp; Сектори &nbsp;&nbsp;
                        {elseif $page eq 'object_zones'} <span class="ui-icon ui-icon-cubes ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp;&nbsp;&nbsp; Зони &nbsp;&nbsp;&nbsp;&nbsp;
                        {elseif $page eq 'object_users'} <span class="ui-icon ui-icon-user ui-object-tab-icon" aria-hidden="true"></span><br/> Потребители
                        {else} <span class="ui-icon ui-icon-signal ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; СОД &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {/if}
                    </a>
                    <div class="dropdown-menu">

                        {if $view.object_messages_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_messages');" id='object_messages'>
                                <span class="ui-icon ui-icon-signal" aria-hidden="true"></span> &nbsp; Сигнали &nbsp;
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_sectors');" id='object_sectors'>
                                <span class="ui-icon ui-icon-cube" aria-hidden="true"></span> &nbsp; Сектори &nbsp;
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_zones');" id='object_zones'>
                                <span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> &nbsp; Зони &nbsp;
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_users');" id='object_users'>
                                <span class="ui-icon ui-icon-user" aria-hidden="true"></span> &nbsp; Потребители &nbsp; &nbsp; </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-signal" aria-hidden="true"></span> &nbsp; Сигнали &nbsp; </a>
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-cube" aria-hidden="true"></span> &nbsp; Сектори &nbsp; </a>
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> &nbsp; Зони &nbsp; </a>
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-user" aria-hidden="true"></span> &nbsp; Потребители &nbsp; &nbsp; </a>
                        {/if}


                    {*{if $key == 'object_archiv_view'}*}
                        {*Архив*}
                        {if $view.object_archiv_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_archiv');" id='object_archiv'><span class="ui-icon ui-icon-archive" aria-hidden="true"></span> &nbsp; Събития &nbsp; </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><span class="ui-icon ui-icon-archive" aria-hidden="true"></span> &nbsp; Събития &nbsp; </a>
                        {/if}
                    {*{/if}*}
                    </div>
                </li>
			{/if}

            {if $key == 'object_geo'}
                {if $page eq 'object_geo'}
                    <li class="nav-item text-center" title="Карта..."><a class="nav-link active"><span class="ui-icon ui-icon-map ui-object-tab-icon" aria-hidden="true"></span><br />Карта</a></li>
                {else}
                    <li class="nav-item text-center" title="Карта..." >{if $view.object_geo}<a class="nav-link" href="#" onclick="return tab_href('object_geo');" id='object_geo'><span class="ui-icon ui-icon-map ui-object-tab-icon" aria-hidden="true"></span><br />Карта</a>{else}<a class="nav-link disabled" onclick="return false;"><span class="ui-icon ui-icon-map ui-object-tab-icon" aria-hidden="true"></span><br />Карта</a>{/if}</li>
                {/if}
            {/if}

            {if $key == 'object_support_view'}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">

                        {if $page eq 'object_support'}<span class="ui-icon ui-icon-wrench ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp;&nbsp; Обслужване &nbsp;&nbsp;&nbsp;
                        {elseif $page eq 'object_store'} <span class="ui-icon ui-icon-warehouse ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp; Склад &nbsp;&nbsp;
                        {else} <span class="ui-icon ui-icon-wrench ui-object-tab-icon" aria-hidden="true"></span><br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; СЕРВИЗ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {/if}
                    </a>
                    <div class="dropdown-menu">

                        {* Обслужване *}
                        {if $page eq 'object_support'}
                            <a class="dropdown-item"  href="#" onclick="return false;">Обслужване</a>
                        {else}
                            {if $view.object_support_view}<a class="dropdown-item text-puzzle px-1" href="#" onclick="return tab_href('object_support');" id='object_support'><span class="ui-icon ui-icon-wrench" aria-hidden="true"></span> Обслужване</a>
                            {else}<a class="dropdown-item text-puzzle disabled px-1"><span class="ui-icon ui-icon-wrench" aria-hidden="true"></span> Обслужване</a>{/if}
                        {/if}

                        {* Склад *}
{*                        {if $page eq 'object_store'}*}
{*                        {else}*}
                            {if $view.object_store_view}<a class="dropdown-item text-puzzle px-1" href="#" onclick="return tab_href('object_store');" id='object_store'><span class="ui-icon ui-icon-warehouse" aria-hidden="true"></span> Склад</a>
                            {else}<a class="dropdown-item text-puzzle disabled px-1"><span class="ui-icon ui-icon-warehouse" aria-hidden="true"></span> Склад</a>{/if}
{*                        {/if}*}

                    </div>
                </li>


            {/if}
		{/foreach}

        <li class="nav-item dropdown ml-auto mr-5">
            {if $isService}
                <a class="nav-link dropdown-toggle text-warning text-center h-100" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><span class="ui-icon ui-icon-settings ui-object-tab-icon" aria-hidden="true"></span></a>
            {else}
                <a class="nav-link dropdown-toggle text-center h-100" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><span class="ui-icon ui-icon-settings ui-object-tab-icon" aria-hidden="true"></span></a>
            {/if}
            <div class="dropdown-menu">
                {if $bEditStatuses}
                    <a class="dropdown-item text-puzzle" onClick="ServiceStatus();"><span class="ui-icon ui-icon-eye" aria-hidden="true"></span> &nbsp; Байпас &nbsp; </a>
                {/if}
                <a class="dropdown-item text-puzzle" href="#" onClick="makePayment();"><span class="ui-icon ui-icon-card" aria-hidden="true"></span> &nbsp; Плащане &nbsp; </a>
                <a class="dropdown-item text-puzzle" href="#" onClick="TechRequestForObject();"><span class="ui-icon ui-icon-wrench" aria-hidden="true"></span> &nbsp; Задача &nbsp; </a>
            </div>
        </li>
</ul>

