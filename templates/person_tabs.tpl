{literal}
<script>
function tab_href (page) {
	if (document.getElementById('id').value<=0) {
		alert ("Служитела все още не съществува в системата!");
		return false;
	}
	obj = document.getElementById(page);
	obj.href = "page.php?page="+page+"&id="+document.getElementById('id').value+"&enable_refresh="+document.getElementById('nEnableRefresh').value;
	return true;
};
</script>
{/literal}
<div  id="search" >
<table cellspacing="0" cellpadding="0" width="100%" id="tabs">
       <tr>
          <td width="1" id="passive"></td>

              {if $page eq personInfo}
                    <td id="active" style="width:120px;">Информация</td>
              {else}
                    <td id="inactive" style="width:120px;">{if $tabs.info}<a href="#" onclick="return tab_href('personInfo');" id='personInfo'>Информация</a>{else}Информация{/if}</td>
              {/if}
              <td width="1" id="passive"></td>
              		
			{* Служебни данни *}	  
              {if $page eq person_data}
                   <td id="active" style="width:125px;">Служебни данни</td>
              {else}
                   <td id="inactive" style="width:125px;">{if $tabs.data}<a href="#" onclick="return tab_href('person_data');" id='person_data'>Служебни данни</a>{else}Служебни данни{/if}</td>
              {/if}
			  <td width="1" id="passive"></td>

			{* Документи *}	  
              {if $page eq person_docs}
                   <td id="active" style="width:125px;">Документи</td>
              {else}
                   <td id="inactive" style="width:125px;">{if $tabs.docs}<a href="#" onclick="return tab_href('person_docs');" id='person_docs'>Документи</a>{else}Документи{/if}</td>
              {/if}
			  <td width="1" id="passive"></td>

			{* Атестации *}	  
              {if $page eq client_orders}
                   <td id="active" style="width:120px;">Атестации</td>
              {else}
                   <td id="inactive" style="width:120px;">{if $tabs.ates}<a href="#" onclick="return tab_href('client_orders');" id='client_orders'>Атестации</a>{else}Атестации{/if}</td>
              {/if}
			  <td width="1" id="passive"></td>

			{* Трудов договор *}	  
              {if $page eq person_contract}
                   <td id="active" style="width:125px;">Трудов договор</td>
              {else}
                   <td id="inactive" style="width:125px;">{if $tabs.contr}<a href="#" onclick="return tab_href('person_contract');" id='person_contract'>Трудов договор</a>{else}Трудов договор{/if}</td>
              {/if}			
              <td width="1" id="passive"></td>
              
            {* Отпуск *}	  
              {if $page eq person_leave}
                   <td id="active" style="width:100px;">Отпуск</td>
              {else}
                   <td id="inactive" style="width:100px;">{if $tabs.leave}<a href="#" onclick="return tab_href('person_leave');" id='person_leave'>Отпуск</a>{else}Отпуск{/if}</td>
              {/if}
              <td width="1" id="passive"></td>

			{* Активи *}	  
              {if $page eq person_actives}
                   <td id="active" style="width:80px;">Активи</td>
              {else}
                   <td id="inactive" style="width:80px;">{if $tabs.activ}<a href="#" onclick="return tab_href('person_actives');" id='person_actives'>Активи</a>{else}Активи{/if}</td>
              {/if}			
              <td width="1" id="passive"></td>


			{* Работна заплата *}
              {if $page eq person_salary}
                   <td id="active" style="width:125px;">Работна заплата</td>
              {else}
                   <td id="inactive" style="width:125px;">{if $tabs.salary}<a href="#" onclick="return tab_href('person_salary');" id='person_salary'>Работна заплата</a>{else}Работна заплата{/if}</td>
              {/if}			
              <td width="1" id="passive"></td>
			
             {* Доклад РМ *}
              {if $page eq person_reports}
                   <td id="active" style="width:125px;">Доклад РМ</td>
              {else}
                   <td id="inactive" style="width:125px;">{if $tabs.reports}<a href="#" onclick="return tab_href('person_reports');" id='person_reports'>Доклад РМ</a>{else}Доклад РМ{/if}</td>
              {/if}
              <td width="1" id="passive"></td>
              
             {* Обаждания *}
              {if $page eq person_calls}
                   <td id="active" style="width:125px;">Обаждания</td>
              {else}
                   <td id="inactive" style="width:125px;">{if $tabs.calls}<a href="#" onclick="return tab_href('person_calls');" id='person_calls'>Обаждания</a>{else}Обаждания{/if}</td>
              {/if}
              <td width="1" id="passive"></td>
              
          <td id="lpassive"></td>
       </tr>
</table>
</div>