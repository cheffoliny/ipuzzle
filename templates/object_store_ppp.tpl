<body style="background: none;">
{literal}
	<script>
		rpc_debug = true;
		
		function openPPP( id )
		{
			var params = 'id=' + id;
			dialogPPP2( params );
		}
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;" style="background: none;">
	<input type="hidden" id="nID" name="nID" value="6005553" />
	
	<table class="page_data" >
	<tr>
		<td style="color: #204060; text-align: left; width: 100%; padding: 1px 0 1px 1px; border: 1px solid #fff;">
			Приемо-предаване
		</td>
	</tr>
	</table>
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
</form>

<script>
	{literal}
		if( parent.document.getElementById( 'nID' ).value )
		{
			$("nID").value = parent.document.getElementById( 'nID' ).value;
		}
	{/literal}
	
	loadXMLDoc2( 'result' );
	
	{if !$edit}
		{literal}
			if( form=document.getElementById( 'form1' ) )
			{
				for( i = 0; i < form.elements.length - 1; i++ )form.elements[i].setAttribute( 'disabled', 'disabled' );
			}
		{/literal}
	{/if}
</script>