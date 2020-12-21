<script>
{literal}

	rpc_debug = true;
	
	function editTiming( id )
	{
		dialogSetTechTiming( id );
	}

{/literal}
</script>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class="page_data">
		<tr>
			<td class="page_name">Технически Обслужвания</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>