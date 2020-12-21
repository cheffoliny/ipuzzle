{literal}
<script>

	function tab_href( sPageID ) 
	{
		$( sPageID ).href = "page.php?page="+ sPageID + "&nID=" + $('nID').value;
		return true; 	
	};

	function changeCheck(tab) {

		try {
			var chng = document.getElementById('chng');
			
			if ( chng.value == 1 ) {
				if ( confirm('Има промени по папката! Желаете ли да ги запишите?') ) {
					submit_form();
					tab_href(tab);	
				} else tab_href(tab);
			} else tab_href(tab);
		}
		catch(err) {
			//alert(err.description);
			tab_href(tab);
		}
	}
</script>

<style>
	#active,
	#inactive
{
	width:140px !important;
}
</style>

{/literal}
<div id="search">
	<table cellspacing="0" cellpadding="0" width="100%" id="tabs">
       <tr>
          <td width="1" id="passive"></td>

          	  {* Информация *}	
              {if $page eq limit_card_info}
                    <td id="active">Информация</td>
              {else}
                    <td id="inactive">
                    	<a href="#" onclick="return tab_href('limit_card_info');" id="limit_card_info">Информация</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
              {* Служители *}	
              {if $page eq limit_card_persons}
                    <td id="active">Служители</td>
              {else}
                    <td id="inactive">
                    	<a href="#" onclick="return changeCheck('limit_card_persons'); " id="limit_card_persons">Служители</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
              {* ППП *}
              {if $page eq limit_card_ppp}
                    <td id="active">ППП</td>
              {else}
                    <td id="inactive">
                    	<a href="#" onclick="return tab_href('limit_card_ppp');" id="limit_card_ppp">ППП</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>
              
              {* Материални запаси *}
              {if $page eq limit_card_mz}
                    <td id="active">Материални запаси</td>
              {else}
                    <td id="inactive">
                    	<a href="#" onclick="return tab_href('limit_card_mz');" id="limit_card_mz">Материални запаси</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>

              {* Операции *}
              {if $page eq limit_card_operations}
                    <td id="active">Операции</td>
              {else}
                    <td id="inactive">
                    	<a href="#" onclick="return tab_href('limit_card_operations');" id="limit_card_operations">Операции</a>
               		</td>
              {/if}
              
              <td width="1" id="passive"></td>

          <td id="lpassive">&nbsp;</td>
       </tr>
	</table>
</div>