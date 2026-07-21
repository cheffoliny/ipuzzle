{literal}
	<script>
		rpc_debug = true;
		
		function openDirection( id )
		{
			dialogSetSetupDirection( "id=" + id, id );
		}
		
		function deleteDirection( id )
		{
			if( confirm( "Наистина ли желаете да премахнете записа?" ) )
			{
				$("nID").value = id;
				
				rpc_on_exit = function()
				{
					var bPermitDelete = false;
					
					if( $("nIsInOffice").value == "1" )
					{
						bPermitDelete = confirm( "Направлението е привързано към един или повече региони.\n Желаете ли да го премахнете въпреки това?" );
					}
					else bPermitDelete = true;
					
					if( bPermitDelete )
					{
						loadXMLDoc2( "delete", 1 );
					}
					
					rpc_on_exit = function() {}
				}
				
				loadXMLDoc2( "checkRegions" );
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onsubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	<input type="hidden" name="nIsInOffice" id="nIsInOffice" value="0" />
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Направления</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openDirection( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( "result" );
</script>
