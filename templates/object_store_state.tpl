{literal}
	<script>
		rpc_debug = true;
		
	</script>
{/literal}

<form name="form1" id="form1" class="w-100 h-100 p-0 m-0 ui-object-store-frame-form" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	<div id="result" class="ui-object-store-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="on"></div>
</form>

<script>
	{literal}
		var oParentID = parent.document.getElementById( 'nID' );
		if( oParentID && oParentID.value )
		{
			$("nID").value = oParentID.value;
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
