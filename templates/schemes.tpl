{literal}
	<script>
		rpc_debug = true;
		
		function editScheme( id )
		{
			dialogScheme( id );
		}
		
		function deleteScheme( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете шаблона?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-scheme-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Номенклатури - ШАБЛОНИ</td>
			<td class="buttons">
				{if $right_edit}<button class="search" onclick="editScheme( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>
