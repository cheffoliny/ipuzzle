{literal}
	<script>
		rpc_debug = true;
		
		function openLeave(id) {
			var person = document.getElementById('id').value;
			dialogLeave(id, person);
		}
		
		function openPersonLeave( id )
		{
			var id_person = document.getElementById( 'id' ).value;
			
			dialogSetupPersonLeave( id, id_person );
		}

		function delLeave(id) {
			if( confirm('Наистина ли желаете да премахнете записа?') ) {
				document.getElementById('idc').value = id;
				loadXMLDoc('delete', 1);
				document.getElementById('idc').value = 0;
			}
		}
		
		function openApplication(id) {
			var person = document.getElementById('id').value;
			dialogApplication( id, person );
		}

		function openHospital(id) {
			var person = document.getElementById('id').value;
			dialogHospital( id, person );
		}
		
		function openQuittance( id )
		{
			var person = document.getElementById('id').value;
			dialogQuittance( id, person );
		}
	
		function rpcEnd( oCallerHandle )
		{
			rpc_on_exit = function()
			{
				if( oCallerHandle )oCallerHandle.focus();
				
				rpc_on_exit = function() {}
			}
			
			loadXMLDoc( "result" );
		}
	</script>
{/literal}

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="id" name="id" value="{$id|default:0}" />
		<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
		<input type="hidden" id="idc" name="idc" value="0" />
		
		<div class="page_caption">Отпуски на {$person_name}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=person_tabs.tpl}</td>
			</tr>
			<tr class="odd">
				<td>
					<table class="input">
						<tr>
							<td align="left">
								<input type="checkbox" name="nIsSubstituteNeeded" id="nIsSubstituteNeeded" class="clear" title="Иска ли се посочване на заместник." onclick="loadXMLDoc( 'save' );" />&nbsp;
								Посочва се заместник
							</td>
							<td align="right">
								<button class="search" onclick="return openPersonLeave( 0 );"><img src="images/plus.gif"/>Нова Молба</button>
								<!-- <button class="search" onclick="return openLeave(0);"><img src="images/plus.gif"/>Отпуск</button> -->
								<button class="search" onclick="return openApplication(0);">Молби за Отпуск</button>
								<button class="search" onclick="return openHospital(0);"><img src="images/plus.gif"/>Болничен</button>
								<button class="search" onclick="return openQuittance(0);"><img src="images/plus.gif"/>Обезщетение</button>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<hr>
							</td>
						</tr>
						<tr>
							<td colspan="2"><div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width: 985px; height: 370px; overflow: auto;"></div></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<div id="search" style="width: 1000px;">
		<table width="100%" cellspacing=1px>
			<tr valign="top">
				<td valign="top" align="right" width="1000px">
					<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
				</td>
			</tr>
		</table>
	</div>
		
	</form>
</div>

<script>
{if !$personnel_edit}
	if( form=document.getElementById('form1') )  
		for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
{/if}
loadXMLDoc('result');
</script>