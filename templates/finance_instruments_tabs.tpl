{literal}
<script>

function tab_href( sPageID ) 
{
	$( sPageID ).href = "page.php?page="+ sPageID + "&entered=1";
	return true; 	
};

</script>

<style>
	#active,
	#inactive
{
	width:100px;
}
</style>

{/literal}

<div  id="search">
    <ul class="nav nav-pills nav-left" style="float: right; border-bottom: 1px solid #fff;">

          	  {* Експорт на Документи за продажба [приход] *}
              {if $page eq export_sale_docs}
                    <li style="background-color: #b61247; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Експорт Приходни док.</a></li>
              {else}
                    <li style="background-color: #b61247; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;" href="#" onclick="return tab_href('export_sale_docs');" id="export_sale_docs">Експорт Приходни док.</a></li>
              {/if}

              
              {* Експорт на Документи за покупка [разход] *}	
              {if $page eq export_buy_docs}
                    <li style="background-color: #cc421c; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Експорт Разходни док.</a></li>
              {else}
                    <li style="background-color: #cc421c; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;" href="#" onclick="return tab_href('export_buy_docs');" id="export_buy_docs">Експорт Разходни док.</a></li>
              {/if}

              
              {* Изпращане на фактури по имейл
              {if $page eq email_invoice}
                    <li style="background-color: #5330b3; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Разпращане по имейл</a></li>
              {else}
                    <li style="background-color: #5330b3; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Разпращане по имейл</a></li>
{*                    	<a href="#" onclick="return tab_href('email_invoice');" id="email_invoice">Разпращане по имейл</a>
              {/if}*}

              
              {* Разпращане на фактури по email - ИСТОРИЯ
              {if $page eq email_invoice_history}
              {*      <td id="active" style="width:200px;">Разпращане по имейл - история</td>
              {else}
              {*      	<a href="#" onclick="return tab_href('email_invoice_history');" id="email_invoice_history">Разпращане по имейл - история</a>
              {/if}*}


              {* Финанси - Обобщена справка
              {if $page eq summary_object_finances_main}
                    <li style="background-color: #a000a7; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Обобщена справка</a></li>
              {else}
                    <li style="background-color: #a000a7; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;"  href="#" onclick="return tab_href('summary_object_finances_main');" id="summary_object_finances_main">Обобщена справка</a></li>
              {/if}*}

              
              {* Отстъпки
              {if $page eq setup_concessions}
                  <li style="background-color: #009100; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Отстъпки</a></li>
              {else}
                  <li style="background-color: #009100; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Отстъпки</a></li>
              {*      	<a href="#" onclick="return tab_href('setup_concessions');" id="setup_concessions">Отстъпки</a>
              {/if}*}


              {* Рекламно Каре *}	
              {if $page eq advert_squares}
              {*      <td id="active" style="width:110px;">Рекламно Каре</td>*}
              {else}
              {*      	<a href="#" onclick="return tab_href('advert_squares');" id="advert_squares">Рекламно Каре</a>*}
              {/if}


			  {* кочани *}	
              {if $page eq books}
                    <li style="background-color: #008ea8; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Кочани</a></li>
              {else}
                    <li style="background-color: #008ea8; border: solid #fff; border-width: 1px 1px 0 1px;"><a style="color: #fff;">Кочани</a></li>
                  {*<a href="#" onclick="return tab_href('books');" id="books">Кочани</a>*}
              {/if}

    </ul>
</div>