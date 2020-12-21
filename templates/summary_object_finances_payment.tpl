<script>
	rpc_debug = true;
	rpc_autonumber = "off";
</script>

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="nIDFirm" name="nIDFirm" value="0">
	<input type="hidden" id="nInterval" name="nInterval" value="6">
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
</form>

{literal}
	<script>
		//Filter
		var oIDFirmCurrent = document.getElementById( "nIDFirm" );
		var oIDFirmParent = parent.document.getElementById( "nIDFirm" );
		var oIntervalCurrent = document.getElementById( "nInterval" );
		var oIntervalParent = parent.document.getElementById( "nInterval" );
		
		if( oIDFirmCurrent && oIDFirmParent )
		{
			oIDFirmCurrent.value = oIDFirmParent.value;
		}
		
		if( oIntervalCurrent && oIntervalParent )
		{
			oIntervalCurrent.value = oIntervalParent.value;
		}
		//End Filter
		
		loadXMLDoc2( 'result' );
	</script>
{/literal}