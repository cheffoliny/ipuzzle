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

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data">
		<tr>
			<td class="page_name">Сметки</td>
			<td class="buttons">
				<button onclick="openAccount( 0 );"><img src="images/plus.gif"> Добави </button>
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2( 'result' );
</script>