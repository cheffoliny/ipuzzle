{literal}
	<script>
		rpc_debug = true;
		
		function editTechSupport( id )
		{
			dialogTechSupport( id );
		}
		
		function deleteTechSupport( id )
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
			<td class="page_name">Настройки - Техническо обслужване</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>