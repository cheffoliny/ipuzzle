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
    //var objectId = $('nID').value;
    dialogTechRequestForObject(0, oID.value);

}

</script>
{/literal}

<div class="row navbar-dark bg-faded">
    <div class="col-sm-8 col-lg-8">
        <h5>
        {if $isService}
            <span id="head_window" class="text-warning text-truncate text-uppercase ml-4" ><i class="fas fa-home"></i> {$object}</span>
        {else}
            <span id="head_window" class="text-white text-truncate text-uppercase ml-4"><i class="fas fa-home"></i> {$object}</span>
        {/if}
        </h5>
    </div>
    <div class="col-sm-4 col-lg-4">


    </div>
</div>

<ul class="nav nav-tabs navbar-dark bg-faded mb-1">

		{foreach key=key item=item from=$view name=rights}

			{if $key == 'object_info_view'}
				{if $page eq object_info}
                    <li class="nav-item text-center" title="Информация"><a class="nav-link active" href="#"><i class="fa fa-info fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Инфо &nbsp;&nbsp;</a></li>
				{else}
					<li class="nav-item text-center" title="Информация">{if $view.object_info_view}<a class="nav-link" href="#" onclick="return tab_href('object_info');" id='object_info'><i class="fa fa-info fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Инфо &nbsp;&nbsp;</a>{else}<a class="nav-link disabled" href="#"><i class="fa fa-info fa-lg ml-3 mr-3"></i><br/>&nbsp;&nbsp; Инфо &nbsp;&nbsp;</a>{/if}</li>
				{/if}
			{/if}


            {if $isFO and  $key == 'object_shifts_view' }
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">

                        {if $page eq object_shifts} <i class="fas fa-tags fa-lg ml-2 mr-2"></i><br/> Тип смени &nbsp;&nbsp;
                        {elseif $page eq object_duty} <i class="fas fa-sync-alt fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp; Смяна&nbsp;&nbsp;
                        {elseif $page eq object_personnel_schedule } <i class="far fa-user fa-lg ml-2 mr-2"></i><br/> В график
                        {else} <i class="fa fa-calendar fa-lg ml-2 mr-2"></i><br/> ГРАФИК
                        {/if}
                    </a>
                    <div class="dropdown-menu">
                        {if $view.object_shifts_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_shifts');" id='object_shifts'><i class="fa fa-tags"></i> Тип смени </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><i class="fa fa-tags"></i> Тип смени </a>
                        {/if}
                        {if $view.object_personnel_schedule_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_personnel_schedule');" id='object_personnel_schedule'><i class="far fa-users"></i> В график </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><i class="far fa-users"></i> В график </a>
                        {/if}
                        {if $view.object_duty_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_duty');" id='object_duty'><i class="fa fa-sync-alt"></i> Смяна </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><i class="fa fa-sync-alt"></i> Смяна </a>
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
                    {*<li class="nav-item" ><a class="nav-link active"><i class="fa fa-group"></i></a></li>*}
				{*{else}*}
                    {*<li class="nav-item" >{if $view.object_personnel_view}<a class="nav-link" href="#" onclick="return tab_href('object_personnel');" id='object_personnel'><i class="fa fa-group"></i></a>{else}<a class="nav-link disabled"><i class="fa fa-group"></i></a>{/if}</li>*}
				{*{/if}*}
					{**}
			{*{/if} *}

			{if $key == 'object_contract_view'}
                {if $page eq object_contract}
                    <li class="nav-item text-center" title="Договор..."><a class="nav-link active"><i class="fa fa-certificate fa-lg"></i><br />Договор</a></li>
				{else}
                    <li class="nav-item text-center" title="Договор..." >{if $view.object_contract_view}<a class="nav-link" href="#" onclick="return tab_href('object_contract');" id='object_contract'><i class="fa fa-certificate fa-lg"></i><br />Договор</a>{else}<a class="nav-link disabled"><i class="fa fa-certificate fa-lg"></i><br />Договор</a>{/if}</li>
				{/if}

			{/if}

			{if $key == 'object_taxes_view'}
                {*Такси 	  *}
                {if $page eq object_taxes}
                    <li class="nav-item text-center" title="Такси"><a class="nav-link active"><i class="fas fa-euro-sign fa-lg ml-3 mr-3"></i><br/>&nbsp; Такси &nbsp;</a></li>
				{else}
                    <li class="nav-item text-center" title="Такси" >{if $view.object_taxes_view}<a class="nav-link" href="#" onclick="return tab_href('object_taxes');" id='object_taxes'><i class="fas fa-euro-sign fa-lg ml-3 mr-3"></i><br/>&nbsp; Такси &nbsp;</a>{else}<a class="nav-link disabled" href="#"><i class="fas fa-euro-sign fa-lg ml-3 mr-3"></i><br/>&nbsp; Такси &nbsp;</a>{/if}</li>
				{/if}

			{/if}

            {if $isSOD &&  $key == 'object_messages_view'}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">

                        {if $page eq object_archiv}<i class="fab fa-stack-overflow fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp;&nbsp; Събития &nbsp;&nbsp;&nbsp;
                        {elseif $page eq object_messages} <i class="fas fa-signal fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp; Сигнали &nbsp;&nbsp;
                        {elseif $page eq object_sectors} <i class="fas fa-cube fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp; Сектори &nbsp;&nbsp;
                        {elseif $page eq object_zones} <i class="fas fa-cubes fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp;&nbsp;&nbsp; Зони &nbsp;&nbsp;&nbsp;&nbsp;
                        {elseif $page eq object_users} <i class="far fa-user fa-lg ml-2 mr-2"></i><br/> Потребители
                        {else} <i class="fa fa-rss fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; СОД &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {/if}
                    </a>
                    <div class="dropdown-menu">

                        {if $view.object_messages_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_messages');" id='object_messages'>
                                <i class="fas fa-signal"></i> &nbsp; Сигнали &nbsp;
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_sectors');" id='object_sectors'>
                                <i class="fas fa-cube"></i> &nbsp; Сектори &nbsp;
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_zones');" id='object_zones'>
                                <i class="fas fa-cubes"></i> &nbsp; Зони &nbsp;
                            </a>
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_users');" id='object_users'>
                                <i class="far fa-user"></i> &nbsp; Потребители &nbsp; &nbsp; </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><i class="fas fa-signal"></i> &nbsp; Сигнали &nbsp; </a>
                            <a class="dropdown-item text-puzzle disabled"><i class="fas fa-cube"></i> &nbsp; Сектори &nbsp; </a>
                            <a class="dropdown-item text-puzzle disabled"><i class="fas fa-cubes"></i> &nbsp; Зони &nbsp; </a>
                            <a class="dropdown-item text-puzzle disabled"><i class="far fa-user"></i> &nbsp; Потребители &nbsp; &nbsp; </a>
                        {/if}


                    {*{if $key == 'object_archiv_view'}*}
                        {*Архив*}
                        {if $view.object_archiv_view}
                            <a class="dropdown-item text-puzzle" href="#" onclick="return tab_href('object_archiv');" id='object_archiv'><i class="fab fa-stack-overflow"></i> &nbsp; Събития &nbsp; </a>
                        {else}
                            <a class="dropdown-item text-puzzle disabled"><i class="fab fa-stack-overflow"></i> &nbsp; Събития &nbsp; </a>
                        {/if}
                    {*{/if}*}
                    </div>
                </li>
			{/if}



            {if $key == 'object_support_view'}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">

                        {if $page eq object_support}<i class="fab fa-whmcs fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp;&nbsp; Обслужване &nbsp;&nbsp;&nbsp;
                        {elseif $page eq object_store} <i class="fas fa-warehouse-alt fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp; Склад &nbsp;&nbsp;
                        {else} <i class="fab fa-whmcs fa-lg ml-2 mr-2"></i><br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; СЕРВИЗ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {/if}
                    </a>
                    <div class="dropdown-menu">

                        {* Обслужване *}
                        {if $page eq 'object_support'}
                            <a class="dropdown-item"  href="#" onclick="return false;">Обслужване</a>
                        {else}
                            {if $view.object_support_view}<a class="dropdown-item text-puzzle px-1" href="#" onclick="return tab_href('object_support');" id='object_support'><i class="fab fa-whmcs fa-lg ml-1 mr-1"></i> Обслужване</a>
                            {else}<a class="dropdown-item text-puzzle disabled px-1"><i class="fab fa-whmcs fa-lg ml-2 mr-2"></i> Обслужване</a>{/if}
                        {/if}

                        {* Склад *}
{*                        {if $page eq 'object_store'}*}
{*                            <a class="dropdown-item active" href="#" onclick="return false;"><i class="fab fa-gears fa-lg ml-2 mr-2"></i> Склад</a>*}
{*                        {else}*}
                            {if $view.object_store_view}<a class="dropdown-item text-puzzle px-1" href="#" onclick="return tab_href('object_store');" id='object_store'><i class="fas fa-warehouse-alt fa-lg ml-2 mr-2"></i> Склад</a>
                            {else}<a class="dropdown-item text-puzzle disabled px-1"><i class="fas fa-warehouse-alt fa-lg ml-2 mr-2"></i> Склад</a>{/if}
{*                        {/if}*}

                    </div>
                </li>

            {/if}
		{/foreach}

        <li class="nav-item dropdown ml-auto mr-5">
            {if $isService}
                <a class="nav-link dropdown-toggle text-warning text-center h-100" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-cog fa-lg ml-2 mr-2"></i></a>
            {else}
                <a class="nav-link dropdown-toggle text-center h-100" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-cog fa-lg ml-2 mr-2"></i></a>
            {/if}
            <div class="dropdown-menu">
                {if $bEditStatuses}
                    <a class="dropdown-item text-puzzle" onClick="ServiceStatus();"><i class="fa fa-low-vision"></i> &nbsp; Байпас &nbsp; </a>
                {/if}
                <a class="dropdown-item text-puzzle" href="#" onClick="makePayment();"><i class="far fa-credit-card"></i> &nbsp; Плащане &nbsp; </a>
                <a class="dropdown-item text-puzzle" href="#" onClick="TechRequestForObject();"><i class="fab fa-whmcs"></i> &nbsp; Задача &nbsp; </a>
            </div>
        </li>
</ul>

