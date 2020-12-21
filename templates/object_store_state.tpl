{literal}
	<script>
		rpc_debug = true;
		
	</script>
{/literal}
<body style="background: none;">
<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />

	<table class="page_data" >
	<tr>
		<td style="color: #204060; text-align: left; width: 100%; padding: 1px 0 1px 1px; border: 1px solid #fff;">
			Техника зачислена на обекта
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