{literal}
	<script>
		rpc_debug = true;
		
		function submit_form()
		{
			loadXMLDoc( 'save', 0 );
		}
		
		/*function delDocument(id)
		{
			if ( confirm( 'Наистина ли желаете да премахнете актива?' ) )
			{
				document.getElementById( 'id_document' ).value = id;
				loadXMLDoc( 'delete', 1 );
				document.getElementById( 'id_document' ).value = 0;
			}
		}*/
		
		function asset_info( id )
		{
			dialogAssetInfo( id );
		}
		
		function modifyGroup( id )
		{	
			if( id )dialogSetGroup( id );
		}
		
		function editPPP()
		{
			dialogAssetsPPP( '0', 'attach' );
		}
		
		function onInit()
		{
			loadXMLDoc2( 'result' );
		}
		
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="id" name="id" value="{$id|default:0}" />
	<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
	<input type="hidden" id="id_document" name="id_document" value="0" />
	
	<div class="page_caption">Активи на {$person_name}</div>
	
	<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
		<tr>
			<td>{include file=person_tabs.tpl}</td>
		</tr>
		<tr>
			<td id="filter_result">
				<!-- начало на работната част -->
				<center>
					<table class="search">
						<tr>
							<td valign="top" align="left" style="width: 830px;">
								Тип:&nbsp;
								<select id="sMyType" name="sMyType" class="select100" onchange="onInit();">
									<option value="full">Подробна</option>
									<option value="group">Групи</option>
								</select>
							</td>
							<td valign="top" align="right">
								<button id="b100" class="search" onClick="editPPP();"><img src="images/plus.gif" />ППП Зачисляване</button>
							</td>
						</tr>
					</table>
				</center>
				
				<hr />
				
				<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width: 1000px; height: 350px; overflow: auto;"></div>
				
			 	<!-- край на работната част -->
			</td>
		</tr>
	</table>
	
	<div id="search" style="padding-top: 10px; width: 1000px;">
		<table width="100%" cellspacing=1px>
			<tr valign="top">
				<td valign="top" align="right" width="1000px">
					<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
				</td>
			</tr>
		</table>
	</div>
	<div id="NoDisplay" style="display: none"></div>
</form>

<script>
	{if !$personnel_edit}
		if( form == document.getElementById( 'form1' ) )
			for( i = 0; i < form.elements.length - 1; i++ )form.elements[i].setAttribute( 'disabled', 'disabled' );
	{/if}
	
	onInit();
	//loadMainData();</script>

</script>