
function FormatEvent( event )
{		
	if( this.isDefined( event.srcElement ) )
	{
		this.srcElement = event.srcElement;
	}
	else if( this.isDefined( event.target ) )
	{
		this.srcElement = event.target;
	}
	else
	{
		this.Throw('Unsupported browser!');
	}
	
	if( this.isDefined( event.which ) )
	{
		this.keyCode = event.which;
	}
	else if( this.isDefined( event.keyCode ) )
	{
		this.keyCode = event.keyCode;
	}
	else
	{
		this.Throw('Unsupported browser!');
	}
	
	if( String.fromCharCode( this.keyCode ) == ',' )
	{
		try {
			event.keyCode = ('.').charCodeAt(0);
		}
		catch( e )
		{
		}
	}
}

FormatEvent.prototype = 
{
	srcElement: null
	,
	keyCode: null
	,
	isIE: function()
	{
		return ( window.navigator.userAgent.indexOf('MSIE') != -1 );
	}
	,
	isDefined: function( el )
	{
		return typeof( el ) != 'undefined';
	}
	,
	hasSelection: function()
	{
		if( document.getSelection ) 
			return ( document.getSelection().length )? true : false;
			
		if(document.selection && document.selection.createRange)
			return ( document.selection.createRange().text.length )? true : false;
    
		return false;
	}
	,
	Throw: function( sMsg )
	{
		throw new Error(null, sMsg);
	}
}

function formatDate( event , separator)
{
	
	if (!separator)
		separator = '/';
		
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}

	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}
	
	if( String.fromCharCode( oEvent.keyCode ) == '-' )
	{
		oEvent.srcElement.value += String.fromCharCode(separator);
		event.returnValue = false;
		return false;
	}	

	if( ( oEvent.keyCode > 0x33 ) && ( oEvent.keyCode <= 0x39 ) && ( !oEvent.srcElement.value.length ) )
	{
		oEvent.srcElement.value += '0';
	}
	
	if( ( oEvent.keyCode > 0x31 ) && ( oEvent.keyCode <= 0x39 ) && ( oEvent.srcElement.value.length == 3 ) )
	{
		oEvent.srcElement.value += '0';
	}
	
	if( oEvent.keyCode == 0x2F )
	{
		switch( oEvent.srcElement.value.length )
		{
			case 1:
			case 4:
				if( oEvent.srcElement.value.slice(-1) != '0' )
				{
					oEvent.srcElement.value = oEvent.srcElement.value.slice(0,-1) + '0' + oEvent.srcElement.value.slice(-1);
				}
				break;
		}
	}

	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );

	var sRegExp = "^($|([0-3]$|(0[1-9]|[12][0-9]|3[01])))($|\separator)($|([01]$|(0[1-9]|1[0-2])))($|\separator)($|[0-9]{1,4})$";
	
	sRegExp = sRegExp.replace('separator', separator); sRegExp = sRegExp.replace('separator', separator)
	
	var oTestValue = new RegExp(sRegExp);
	
	if (!sTestValue.match(oTestValue))	
	{
		event.returnValue = false;
		return false;
	}

	switch( oEvent.srcElement.value.length )
	{
		case 1:
		case 4:
			oEvent.srcElement.value += String.fromCharCode( oEvent.keyCode ) + separator;
			event.returnValue = false;
			return false;
	}

	event.returnValue = true;
	return true;
}

function formatTime( event )
{
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.keyCode == 0x3A )
	{
		switch( oEvent.srcElement.value.length )
		{
			case 0:
				oEvent.srcElement.value = '00';
				break;
			case 1:
				oEvent.srcElement.value = '0' + oEvent.srcElement.value;
				break;
		}
	}
	
	if(( !oEvent.srcElement.value.length ) && ((oEvent.keyCode > 0x32) && (oEvent.keyCode <= 0x39 )))
	{
		oEvent.srcElement.value = '0' + String.fromCharCode( oEvent.keyCode ) + ':';
		event.returnValue = false;
		return false;
	}
		
	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );
	
	if( !(/^($|([0-2]$|([01][0-9]|[2][0-3])))($|:)($|[0-5])($|[0-9])$/).test( sTestValue ) )
	{
		event.returnValue = false;
		return false;
	}
	
	if( oEvent.srcElement.value.length == 1 )
	{
		oEvent.srcElement.value += String.fromCharCode( oEvent.keyCode ) + ':';
		event.returnValue = false;
		return false;
	}
	
	event.returnValue = true;
	return true;
}

function formatTimeS( event )
{
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.keyCode == 0x3A )
	{
		switch( oEvent.srcElement.value.length )
		{
			case 0:
				oEvent.srcElement.value = '00';
				break;
			case 1:
				oEvent.srcElement.value = '0' + oEvent.srcElement.value;
				break;
		}
	}
	
	if(( !oEvent.srcElement.value.length ) && ((oEvent.keyCode > 0x32) && (oEvent.keyCode <= 0x39 )))
	{
		oEvent.srcElement.value = '0' + String.fromCharCode( oEvent.keyCode ) + ':';
		event.returnValue = false;
		return false;
	}
		
	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );
	
	if( !(/^($|([0-2]$|([01][0-9]|[2][0-3])))($|:)($|[0-5])($|[0-9])($|:)($|[0-5])($|[0-9])$/).test( sTestValue ) )
	{
		event.returnValue = false;
		return false;
	}
	
	if( oEvent.srcElement.value.length == 1 || oEvent.srcElement.value.length == 4 )
	{
		oEvent.srcElement.value += String.fromCharCode( oEvent.keyCode ) + ':';
		event.returnValue = false;
		return false;
	}
	
	event.returnValue = true;
	return true;
}


function formatMoney( event )
{	
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}

	if( String.fromCharCode( oEvent.keyCode ) == ',' )
		oEvent.keyCode = ('.').charCodeAt(0);
	
	if( String.fromCharCode( oEvent.keyCode ) == '.' && ( !oEvent.srcElement.value.length ))
	{
		oEvent.srcElement.value = '0';
	}

	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );

	if( !(/^(-?($|[0-9]*)($|\.)($|[0-9]{0,2}))$/).test( sTestValue ) )
	{
		event.returnValue = false;
		return false;
	}
	
	event.returnValue = true;
	return true;
} 

function formatDigits( event )
{
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}

	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}
	
	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );

	if( !(/^[0-9]+$/).test( sTestValue ) )
	{
		event.returnValue = false;
		return false;
	}

	event.returnValue = true;
	return true;
}

function formatNumber( event )
{
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}

	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}
	
	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );

	if( !(/^([1-9\-]($|[0-9]+))$/).test( sTestValue ) )
	{
		event.returnValue = false;
		return false;
	}

	event.returnValue = true;
	return true;
}

function formatWeight( event )
{	
	try {	
		var oEvent = new FormatEvent( event );
	}
	catch( e )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.keyCode < 0x20 )
	{
		event.returnValue = true;
		return true;
	}
	
	if( oEvent.hasSelection() )
	{
		event.returnValue = true;
		return true;
	}

	if( String.fromCharCode( oEvent.keyCode ) == ',' )
		oEvent.keyCode = ('.').charCodeAt(0);
	
	if( String.fromCharCode( oEvent.keyCode ) == '.' && ( !oEvent.srcElement.value.length ))
		oEvent.srcElement.value = '0';
	
	if( String.fromCharCode( oEvent.keyCode ) == '0' && ( !oEvent.srcElement.value.length ))
	{
		oEvent.srcElement.value = '0';
		oEvent.keyCode = ('.').charCodeAt(0);
	}

	var sTestValue = oEvent.srcElement.value + String.fromCharCode( oEvent.keyCode );

	if( !(/^(($|[0-9]+)($|\.)($|[0-9]{0,3}))$/).test( sTestValue ) )
	{
		event.returnValue = false;
		return false;
	}
	
	event.returnValue = true;
	return true;
}