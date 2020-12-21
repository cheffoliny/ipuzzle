{literal}
	<script>
		rpc_debug = true;
		
		function openOrder( id )
		{
			var person = document.getElementById('id').value;
			dialogAttestation( id, person );
		}

		function delOrder( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('to_del').value = id;
				loadXMLDoc( 'delete', 1 );
			}
		}
	</script>
{/literal}

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="id" name="id" value="{$id_person|default:0}" />
		<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
		<input type="hidden" name="to_del" id="to_del" value="0" />
		
		<div class="page_caption">Атестации за {$person_name}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=person_tabs.tpl}</td>
			</tr>
			<tr class="odd">
				<td>
		  			<table class="input">
						<tr class="odd">
							<td colspan=6 valign="top">
								<table class="input">
									<tr>
										<td valign="top" align="right" style="width: 995px;">
											{if $personnel_edit}
												<button class="search" onclick="return openOrder(0);"><img src="images/plus.gif"/> Добави </button>
											{else}
												&nbsp;
											{/if}
											<hr />
										</td>
									<tr>
									<tr>
										<td><div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width: 985px; height: 350px;overflow: auto;"></div></td>
									<tr>
								</table>
							</td>
						</tr>
					</table>
					<table class="input">
						<tr valign="top" class="odd">
							<td valign="top" align="right" width="995px">
								<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>

<script>
	loadXMLDoc( 'result' );
	
	{if !$personnel_edit}
	if( form=document.getElementById( 'form1' ) )  
			for( i = 0; i < form.elements.length - 1; i++ )form.elements[i].setAttribute( 'disabled', 'disabled' );
	{/if}
</script>