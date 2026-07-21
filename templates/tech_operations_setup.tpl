{literal}
	<script>
		rpc_debug = true;
		
		function getResult()
		{
			loadXMLDoc2( 'result' );
		}
		
		function editOperation( id )
		{
			dialogSetTechOperation( id );
		}
		
		function deleteOperation( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете операцията?' ) )
			{
				$('nID').value = id;
				
				rpc_on_exit = function ( nCode )
				{
					if( !parseInt( nCode ) )
					{
						if( parent.document.getElementById('operations_scheme') )
						{
							parent.document.getElementById('operations_scheme').src = 'page.php?page=tech_operations_scheme';
						}
					}
					
					rpc_on_exit = function () {}
				}
				
				loadXMLDoc2( 'delete', 1 );
			}
		}
	</script>
	
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-tech-operations-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Операции</td>
			<td class="buttons">
				{if $right_edit}<button onclick="editOperation( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
</form>

<script>
	getResult();
</script>
