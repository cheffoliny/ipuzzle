{literal}
	<script>
		rpc_debug = true;
		
		function openHoldupReason( id )
		{
			dialogSetHoldupReason( id );
		}
		
		function deleteHoldupReason( id )
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
			<td class="page_name">Причини за Профилактика</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openHoldupReason( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
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
