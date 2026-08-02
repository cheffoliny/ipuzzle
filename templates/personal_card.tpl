<form name="form1" id="form1" class="ui-personal-card-shell" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="earning" name="earning" value="0" />
    <input type="hidden" id="nIDLimitCard" name="nIDLimitCard" value="0" />

    {include file="personal_card_tabs.tpl"}

    <div class="ui-personal-card-workspace">
        <section class="ui-personal-card-panel ui-personal-card-limit-panel" aria-label="Лимитни карти">
            <iframe id="personal_card_limit_card" title="Лимитни карти" src="page.php?page=personal_card_limit_card&id_limit_card={$nIDLimitCard}"></iframe>
        </section>
        <section class="ui-personal-card-panel ui-personal-card-operations-panel" aria-label="Операции">
            <iframe id="personal_card_operations" title="Операции по лимитна карта" src="page.php?page=personal_card_operations&id_limit_card={$nIDLimitCard}"></iframe>
        </section>
        <section class="ui-personal-card-panel ui-personal-card-schedule-panel" aria-label="График">
            <iframe id="personal_card_schedule" title="График" src="page.php?page=personal_card_schedule"></iframe>
        </section>
    </div>
</form>
