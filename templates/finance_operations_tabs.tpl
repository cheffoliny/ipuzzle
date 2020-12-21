{literal}
<script>

	function tab_href( sPageID )
	{
		$( sPageID ).href = "page.php?page="+ sPageID + "&entered=1";
		return true;
	};

</script>
{/literal}


<ul class="nav nav-tabs nav-intelli">

	{* Справка приходи *}
	{if $page eq sales_docs}
		<li class="nav-item text-center" title="Приходи">
			<a class="nav-link active" href="#">Приходи</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Приходи">
			<a class="nav-link" href="#" onclick="return tab_href('sales_docs');" id='sales_docs'>Приходи</a>
		</li>
	{/if}

	{* Справка разходи *}
	{if $page eq buy_docs}
		<li class="nav-item text-center" title="Разходи">
			<a class="nav-link active" href="#">Разходи</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Разходи">
			<a class="nav-link" href="#" onclick="return tab_href('buy_docs');" id="buy_docs">Разходи</a>
		</li>
	{/if}

	{* Отчети *}
	{if $page eq currency_movement}
		<li class="nav-item text-center" title="Отчети">
			<a class="nav-link active" href="#">Отчети</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Отчети">
			<a class="nav-link" href="#" onclick="return tab_href('currency_movement');" id="currency_movement">Отчети</a>
	   </li>
	{/if}

	{*Online Payment*}
	{if $page eq online_payments}
		<li class="nav-item text-center" title="Онлайн плащания">
			<a class="nav-link active" href="#">iPay</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Онлайн плащания">
			<a class="nav-link" href="#" onclick="return tab_href('online_payments');" id="online_payments">iPay</a>
		</li>
	{/if}

	{* Салда - Фирми *}
	{if $page eq view_firm_balances}
		<li class="nav-item text-center" title="Салда">
			<a class="nav-link active" href="#">Салда</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Салда">
			<a class="nav-link" href="#" onclick="return tab_href('view_firm_balances');" id="view_firm_balances">Салда</a>
		</li>
	{/if}

	{* Наличности *}
	{if $page eq view_balance}
		<li class="nav-item text-center" title="Наличности">
			<a class="nav-link active" href="#">Наличности</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Наличности">
			<a class="nav-link" href="#" onclick="return tab_href('view_balance');" id="view_balance">Наличности</a>
		</li>
	{/if}

	{* Парични Потоци – Подробна *}
	{if $page eq view_money_nomenclatures_detail}
		<li class="nav-item text-center" title="Извлечения">
			<a class="nav-link active" href="#">Извлечения</a>
		</li>
	{else}
		<li class="nav-item text-center" title="Извлечения">
			<a class="nav-link" href="#" onclick="return tab_href('view_money_nomenclatures_detail');" id="view_money_nomenclatures_detail">Извлечения</a>
		</li>
	{/if}

</ul>