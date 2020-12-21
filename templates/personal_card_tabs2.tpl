
{literal}
<script>

function tab_href( page ) 
{
	var oID = $('nIDLimitCard');
	
	//if( oID && parseInt( oID.value ) ) {
		obj = document.getElementById(page);
		obj.href = "page.php?page=" + page + "&id_limit_card=" + oID.value;
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

              {if $page eq personal_card_operations}
                    <td id="active" style="width:150px;" nowrap="nowrap" >Операции</td>
              {else}
                    <td id="inactive" style="width:150px;" nowrap="nowrap"><a href="#" onclick="return tab_href('personal_card_operations');" id='personal_card_operations'>Операции</a></td>
              {/if}
              <td width="1" id="passive"></td>
              			  
              {if $page eq personal_card_ppp}
                   <td id="active" style="width:150px;">ППП</td>
              {else}
                   <td id="inactive" style="width:150px;"><a href="#" onclick="return tab_href('personal_card_ppp');" id='personal_card_ppp'>ППП</a></td>
              {/if}
			  <td style="width:70%;" id="passive"></td>

          <td id="lpassive"></td>
       </tr>
</table>
	
</div>