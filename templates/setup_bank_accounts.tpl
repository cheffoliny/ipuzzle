<script>
	{literal}
		rpc_debug = true;
		
		function openAccount( id )
		{
			dialogSetSetupBankAccount( 'id=' + id, id );
		}
		
		function deleteAccount( id )
		{
			if( confirm('Наистина ли желаете да премахнете записа?') )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
	{/literal}
</script>

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Сметки</td>
			<td class="buttons">
				<button onclick="openAccount( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2( 'result' );
</script>
