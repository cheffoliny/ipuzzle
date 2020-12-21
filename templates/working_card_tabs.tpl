{literal}
<script>

function tab_href( sPageID ) 
{
	$( sPageID ).href = "page.php?page="+ sPageID + "&entered=1&nIDCard=" + $('nIDCard').value;
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
<div  id="search" >
	<table cellspacing="0" cellpadding="0" width="100%" id="tabs">
       <tr>
          <td width="1" id="passive"></td>

          	  {* Информация *}	
              {if $page eq working_card_info}
                    <td id="active" style="width:100px;">Информация</td>
              {else}
                    <td id="inactive" style="width:100px;">
                    	<a href="#" onclick="return tab_href('working_card_info');" id="working_card_info">Информация</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
              {* Автопатрули *}	
              {if $page eq working_card_patrol}
                    <td id="active" style="width:100px;">Автопатрули</td>
              {else}
                    <td id="inactive" style="width:100px;">
                    	<a href="#" onclick="return tab_href('working_card_patrol');" id="working_card_patrol">Автопатрули</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
              {* Движение *}
              {if $page eq working_card_movement}
                    <td id="active" style="width:100px;">Движение</td>
              {else}
                    <td id="inactive" style="width:100px;">
                    	<a href="#" onclick="return tab_href('working_card_movement');" id="working_card_movement">Движение</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
			  
              {* Техници *}
              {if $page eq working_card_techs}
                    <td id="active" style="width:100px;">Техници</td>
              {else}
                    <td id="inactive" style="width:100px;">
                    	<a href="#" onclick="return tab_href('working_card_techs');" id="working_card_techs">Техници</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
              {* Празен *}
			  <td id="passive" style="width: 100px;">&nbsp;</td>    
				          
              <td width="1" id="passive"></td>

              {* Справка Работни карти *}
              {if $page eq working_cards}
                    <td id="active" style="width: 100px;">Справка РК</td>
              {else}
                    <td id="inactive" style="width: 100px;">
                    	<a href="#" onclick="return tab_href('working_cards');" id="working_cards">РК: Справки</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>

              {* Движение Работни карти *}
              {if $page eq patruls_movement}
                    <td id="active" style="width: 100px;">Движение РК</td>
              {else}
                    <td id="inactive" style="width: 100px;">
                    	<a href="#" onclick="return tab_href('patruls_movement');" id="patruls_movement">РК: Движение</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
          <td id="lpassive">&nbsp;</td>
       </tr>
	</table>
</div>