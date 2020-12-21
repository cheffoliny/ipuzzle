{literal}
	<script>
		rpc_debug = true;
		
		function openType( id )
		{
			dialogSetObjectType( id );
		}
		
		function deleteType( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data">
		<tr>
			<td class="page_name">Типове на обекти</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openType( 0 );"><img src="images/plus.gif"> Добави </button>
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