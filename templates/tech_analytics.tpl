<script type="text/javascript">
    rpc_debug = true;
    rpc_html_debug = true;
</script>

<form name="form1" id="form1" class="ui-nomenclature-list ui-technical-list ui-tech-analytics" action="page.php?page={$page}" method="POST">
    <table class="page_data ui-nomenclature-heading ui-technical-heading">
        <tr>
            <td class="page_name">АНАЛИТИК</td>
        </tr>
    </table>

    <center class="ui-nomenclature-filter-wrap ui-technical-filter-wrap">
        <table class="search ui-nomenclature-filter ui-technical-filter">
            <tr>
                <td style="width:150px;" align="right">Тип на справката:&nbsp;</td>
                <td style="width:220px;">
                    <select name="nType" id="nType" style="width:200px;">
                        <option value="objects" {if $nType eq 'objects'}selected{/if}>Наситеност на обекти</option>
                        <option value="passable_time" {if $nType eq 'passable_time'}selected{/if}>Проходимост (последните 2 дена)</option>
                    </select>
                </td>
                <td align="left">
                    <label><input id="bObjects" name="bObjects" type="checkbox" value="1" class="clear" {$bObjects} /> Обекти</label>&nbsp;&nbsp;
                    <label><input id="bZoom" name="bZoom" type="checkbox" value="1" class="clear" {$bZoom} /> Мащаб</label>
                </td>
            </tr>
            <tr>
                <td style="width:150px;" align="right">Регион&nbsp;</td>
                <td style="width:220px;">
                    <select name="nOffice" id="nOffice" style="width:200px;">
                        <option value="0">--- Всички ---</option>
                        {foreach item=aOffice from=$aOffices}
                            <option value="{$aOffice.id}" {if $nSelectedOffice eq $aOffice.id}selected{/if}>{$aOffice.name}</option>
                        {/foreach}
                    </select>
                </td>
                <td align="right">
                    <button type="submit" name="Button" class="search"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Покажи</button>
                </td>
            </tr>
        </table>
    </center>
</form>
