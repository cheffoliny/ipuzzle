{literal}
	<script>
		rpc_debug = true;
		
		function newSalaryExpense( id )
		{
			dialogSalaryExpense( id );
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Номенклатури - УДРЪЖКИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="newSalaryExpense( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		loadXMLDoc('result');
		
		function viewSalaryExpense( id )
		{
			newSalaryExpense( id );
		}

		function deleteSalaryExpense( id )
		{
			if( confirm('Наистина ли желаете да премахнете записа?') )
			{
				$('id').value = id;
				loadXMLDoc( 'delete' );
			}
		}
	</script>
{/literal}
