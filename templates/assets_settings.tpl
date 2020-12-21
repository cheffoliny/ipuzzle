{literal}
	<script>
		rpc_debug = true;
		
		function editAssetsSettings( id )
		{
			dialogAssetsSettings( id );
		}
		
		function deleteAssetsSettings( id )
		{
			if( confirm( 'Наистина ли желаете да изтриете настройката?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
	</script>
{/literal}

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	
	<table class="page_data">
		<tr>
			<td class="page_name">Настройки - Активи</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>