
{literal}
<script>

function tab_href( page ) 
{
	var oID = $('nID');
	
	//if( oID && parseInt( oID.value ) ) {
		obj = document.getElementById(page);
		id_limit_card = $('nIDLimitCard').value;
		obj.href = "page.php?page=" + page + "&nID=" + oID.value + "&id_limit_card=" + id_limit_card;
		return true; 	
	//}	
}

</script>

<style>
	#active,
	#inactive
	{
		text-align:center !important;
 		padding: 3px 15px 3px 15px;
	}
	
	#tabs td
	{
		white-space:nowrap !important;
	}
</style>
{/literal}

<div  id="search" >
<table cellspacing="0" cellpadding="0" width="100%" id="tabs">
       <tr>
          <td width="1" id="passive"></td>

              {if $page eq personal_card}
                    <td id="active" style="width:150px;" nowrap="nowrap" >Лимитни&nbsp;карти</td>
              {else}
                    <td id="inactive" style="width:150px;" nowrap="nowrap"><a href="#" onclick="return tab_href('personal_card');" id='personal_card'>Лимитни карти</a></td>
              {/if}
              <td width="1" id="passive"></td>
              			  
              {if $page eq personal_card_salary}
                   <td id="active" style="width:150px;">Заплата</td>
              {else}
                   <td id="inactive" style="width:150px;"><a href="#" onclick="return tab_href('personal_card_salary');" id='personal_card_salary'>Заплата</a></td>
              {/if}
			  <td style="width:70%;" id="passive"></td>

          <td id="lpassive"></td>
       </tr>
</table>
	
</div>