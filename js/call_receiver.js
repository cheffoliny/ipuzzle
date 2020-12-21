function dialogCallReceiver( id, caller_id )
{
//	dialog_win( 'call_receiver&id=' + id + '&caller_id=' + caller_id, 500, 600, 'call_receiver', 'call_receiver' );
}

function checkCalls()
{
	var xmlhttp;
	if( window.XMLHttpRequest )
	{
		xmlhttp = new XMLHttpRequest();
	}
	else if( window.ActiveXObject )
	{
		xmlhttp = new ActiveXObject( "Microsoft.XMLHTTP" );
	}
	else
	{
		alert( "Стара версия на браузъра!" );
	}
	xmlhttp.onreadystatechange = function()
	{
		if( xmlhttp.readyState == 4 )
		{
			if( xmlhttp.responseText != "" )
			{
				var aParams = xmlhttp.responseText.split( "|" );
				
				var nID 		= aParams[0];
				var sCallerID 	= aParams[1];
				
				nID = parseInt( nID );
				
				if( !isNaN( nID ) && sCallerID != null )
				{
					dialogCallReceiver( nID, sCallerID );
				}
			}
		}
	}
	
	xmlhttp.open( "GET", "scripts/call_receiver.php", true );
	xmlhttp.send( null );
	
//	setTimeout( "checkCalls()", 2000 );
}

//setTimeout( "checkCalls()", 2000 );