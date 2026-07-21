{literal}
	<script>
		rpc_debug = true;
		
		function editSetupPositionsNC( id )
		{
			dialogPositionNC( id );
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Номенклатури - ДЛЪЖНОСТИ ПО НКИД</td>
			<td class="buttons">
				{if $right_edit}<button onclick="editSetupPositionsNC( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr />
	
	
	<div id="result"></div>

</form>

{literal}
    <script>
   		loadXMLDoc2('result');
	
   		function viewPositionsNC ( id )
   		{
   			editSetupPositionsNC( id );
   		}
   		
   		function deleteSetupPositionsNC( id )
		{
			if( confirm('Наистина ли желаете да премахнете записа?') )
			{
				$('id').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
    </script>
{/literal}
