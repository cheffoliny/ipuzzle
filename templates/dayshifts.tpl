<script>
{literal}
	rpc_debug = true;
	
	//Functions
	function openObject( id )
	{
		var sParams = new String();
		
		if( id )
		{
			sParams = 'nID=' + id;
			
			dialogObjectInfo( sParams );
		}
	}
	
	function openShift( id )
	{
		var sParams = new String();
		
		if( id )
		{
			sParams = 'nID=' + id;
			
			dialogObjectDuty( sParams );
		}
	}
	
{/literal}
</script>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Смени</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		
		rpc_on_exit = function ( nCode )
		{
			if( !parseInt( nCode ) )
			{
				setTimeout( "loadXMLDoc2('result')", 300000 );
			}
		}
		
		loadXMLDoc2('result');
	</script>
{/literal}