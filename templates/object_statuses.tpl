{literal}
	<script>
		rpc_debug = true;
		
		function openStatus( id )
		{
			dialogSetObjectStatus( id );
		}
		
		function deleteStatus( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Статуси на обекти</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openStatus( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
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
