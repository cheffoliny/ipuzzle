{literal}
	<script>
		rpc_debug = true;
		rpc_autonumber = "off";
		
		rpc_on_exit = function()
		{
			var text = $('result').innerHTML;
			text = text.replace( /posbaro/g, "<img src=\"images/progress_blue.png\" style=\"width: " );
			text = text.replace( /posbarc/g, "px; height: 12px;\" />" );
			text = text.replace( /negbaro/g, "<img src=\"images/progress_red.gif\" style=\"width: " );
			text = text.replace( /negbarc/g, "px; height: 12px;\" />" );
			$('result').innerHTML = text;
		}
		
		function openUnpaidObjects( sPaidTo, firm, office, window )
		{
			var params = 'sPaidTo=' + sPaidTo + '&nIDFirm=' + firm + '&nIDOffice=' + office + '&nWindow=' + window;
			
			dialogObjectOverview( params );
		}
		
		function openObject( toDate, firm, office, window )
		{
			var params = 'dTo=' + toDate + '&nIDFirm=' + firm + '&nIDOffice=' + office + '&nWindow=' + window;
			
			dialogObjectOverview( params );
		}
		
		function openObjects( sDate, is_by_firm, id_firm, id )
		{
			var params = "date=" 		+ sDate;
			params += "&is_by_firm=" 	+ is_by_firm;
			params += "&id_firm=" 		+ id_firm;
			params += "&id=" 			+ id;
			
			dialogSummaryObjectFinancesObjects( params, 'summary_object_finances_objects' );
		}
	</script>
{/literal}

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
		
//		//Call Api
//		var nTransferFromIntraFirst = false;
//
//		if( nTransferFromIntraFirst )
//		{
//			rpc_on_exit = function()
//			{
//				loadXMLDoc2( "result" );
//
//				rpc_on_exit = function() {}
//			}
//			loadXMLDoc2( "dataIntraToTelenet" );
//		}
//		else
//		{
			loadXMLDoc2( "result" );
//		}
		//End Call Api
	</script>
{/literal}