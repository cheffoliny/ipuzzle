{literal}
	<script>
		rpc_debug = true;
		
	</script>
{/literal}

<form name="form1" id="form1" class="w-100 h-100 p-0 m-0" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
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