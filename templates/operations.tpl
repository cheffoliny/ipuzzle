{literal}
	<script>
	
		rpc_debug = true;
		rpc_excel_panel = "off";


		function viewOperation( id )
		{
			dialogOperation( id );
		}
		
		function deleteOperation( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете операцията?' ) )
			{
				$( 'nID' ).value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
	</script>
{/literal}



<form id="form1" name="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<table class="page_data ui-nomenclature-heading">

		<tr>
			<td class="page_name">
				Операции
			</td>
			
			<td align="right" class="buttons">
				<button id="b70" name="Button" onClick="viewOperation( 0 );" ><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
			</td>			
		</tr>
	</table>
	
	<div class="ui-nomenclature-filter-wrap">
	<table class="search table-secondary ui-nomenclature-filter ui-activity-filter">
		<tr>
			<td> Наименование: </td>
			<td>
				<input id="sName" name="sName" />
			</td>
			
			<td> Описание: </td>
			<td>
				<input id="sDesc" name="sDesc" />
			</td>
			<td>&nbsp; &nbsp;</td>
			
			<td align="right" >
				<button id="b70" name="Button" onClick="return loadXMLDoc2( 'result' );" ><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси </button>
			</td>

		</tr>						
	</table>
	</div>
<hr/>
		
	<div id="result"></div>
</form>


{literal}
<script>

	loadXMLDoc2( 'result' );
	
</script>
{/literal}
