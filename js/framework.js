
/**
*	Класове капсулиращи цялата функционалност на клиента.
*
*	@name Framework
* 	@author dido2k
*	@version 0.0.1
*
*	За бога хора не променяйте!
*/

function $( sElement )
{
	if( arguments.length == 1 )
		return document.getElementById( arguments[0] );
	
	var aElements = new Array();

	for(var i=0;i<arguments.length;i++)
		aElements.push( document.getElementById( arguments[i] ) );

	return aElements;
}

function $A( object )
{
	var aResult = new Array();
	
	for(var i=0;i<object.length;i++)
      aResult.push( object[i] );
      
    return aResult;
}

Function.prototype.bind = function() 
{
	var oMehtod = this;
	var aArguments = $A( arguments );
	var oObject = aArguments.shift();
	
	return function() 
	{
		return oMehtod.apply(oObject, aArguments.concat( $A( arguments ) ) );
	}
}

String.prototype.lTrim = function() {
	return this.replace(/^\s+/, "");
}

String.prototype.rTrim = function() {
	return this.replace(/\s+$/, "");
}

String.prototype.trim = function() {
	return this.lTrim().rTrim();
}

function getFramework()
{
	if( !document['__FRAMEWORK__'] )
		document['__FRAMEWORK__'] = new Framework();
		
	return document['__FRAMEWORK__'];
}

Framework = function()
{
	this.oDisabler	= new Framework.Disabler();
	this.oForm		= new Framework.Form();
	this.oConfirm	= new Framework.Confirm();
}

Framework.Version = "0.0.1";

Framework.prototype.serialize = function( oForm )
{
	return this.oForm.serialize( oForm );
}

Framework.prototype.showDisabler = function()
{
	return this.oDisabler.show();
}

Framework.prototype.hideDisabler = function()
{
	return this.oDisabler.hide();
}

Framework.prototype.confirm = function(sQuestion, fnHandler)
{
	return this.oConfirm.show(sQuestion, fnHandler);
}


//Framework.Form

Framework.Form = function()
{
	
}

Framework.Form.prototype.serialize = function( oForm )
{	
	var aPairs = new Array();
	
	for(var i=0;i<oForm.elements.length;i++)
	{
		if( typeof( oForm.elements[i].name ) == "undefined" || !oForm.elements[i].name || oForm.elements[i].name == "" )
			continue;
			
		switch( oForm.elements[i].tagName.toLowerCase() )
		{
			case "input":
				{
					switch( oForm.elements[i].type.toLowerCase() )
					{
						case "radio":
							if( oForm.elements[i].checked  )
								aPairs.push( encodeURIComponent( oForm.elements[i].name ) + '=' + encodeURIComponent( oForm.elements[i].value ) );
							break;
						case "checkbox":
							aPairs.push( encodeURIComponent( oForm.elements[i].name ) + '=' + ( oForm.elements[i].checked ? 1 : 0 ));
							break;
						default:
							aPairs.push( encodeURIComponent( oForm.elements[i].name ) + '=' + encodeURIComponent( oForm.elements[i].value ) );
							break;
					}
				}
				break;
			case "select":
				if( oForm.elements[i].getAttribute("multiple") != null )
				{
					for(var k=0;k<oForm.elements[i].options.length;k++)
					{
						if( oForm.elements[i].options[k].selected )
							aPairs.push( encodeURIComponent( oForm.elements[i].name ) + '=' + encodeURIComponent( oForm.elements[i].options[k].value ) );
					}
				}
				else
				{
					aPairs.push( encodeURIComponent( oForm.elements[i].name ) + '=' + encodeURIComponent( oForm.elements[i].value ) );
				}
				break;
			case "textarea":
				aPairs.push( encodeURIComponent( oForm.elements[i].name ) + '=' + encodeURIComponent( oForm.elements[i].value ) );
				break;
				
		}
	}
	
	return aPairs.join('&');
}

//Framework.ClassNames

Framework.ClassNames = function()
{
}

Framework.ClassNames.prototype.hasClass = function( oElement, sClassName )
{
	if( oElement && oElement.className && oElement.className.length )
		return oElement.className == sClassName || oElement.className.match( new RegExp("\\b" + sClassName + "\\b") );
		
	return false;
}

Framework.ClassNames.prototype.addClass = function( oElement, sClassName )
{
	if( oElement && oElement.className && !this.hasClass( oElement, sClassName ) )
		oElement.className += ' ' + sClassName;
}

Framework.ClassNames.prototype.removeClass = function( oElement, sClassName )
{
	if( oElement && oElement.className && this.hasClass( oElement, sClassName ) )
		oElement.className = oElement.className.replace( new RegExp("\\b" + sClassName + "\\b"), "");
}

//Framework.Disabler

Framework.Disabler = function()
{
	this.sIFrameID	= "Framework.Disabler";
	this.nOpacity = 40;
}

Framework.Disabler.prototype.show = function()
{
	var oIFrame = $( this.sIFrameID );
	
	if( oIFrame == null )
	{
		oIFrame = document.createElement("iframe");
		
		oIFrame.id = this.sIFrameID;
		oIFrame.width = "100%";
		oIFrame.height = "100%";
		oIFrame.style.position = "absolute";
		oIFrame.style.zIndex = "100";
		oIFrame.style.top = 0;
		oIFrame.style.left = 0;
		oIFrame.style['filter'] = "alpha(opacity=" + this.nOpacity + ")";
		oIFrame.style['-moz-opacity'] = "0." + this.nOpacity; 
		oIFrame.style['opacity'] = "0." + this.nOpacity; 
		oIFrame.frameBorder = 0;
		
		document.body.appendChild( oIFrame );
	}
	else
	{
		oIFrame.style.display = "inline";
	}
	
	// window.focus();
	//( oIFrame.contentWindow || oIFrame ).focus();
}

Framework.Disabler.prototype.hide = function()
{		
	$( this.sIFrameID ).style.display = "none";
}

Framework.Disabler.prototype.getFrame = function()
{
	return $( this.sIFrameID );
}


/*
//Framework.Blender
Framework.Blender = function()
{
	this.sID = 'FRAMEWORK_BLENDER';
}

Framework.Blender.prototype.show = function()
{
	var oDiv = $( this.sID );
	
	if( !oDiv )
	{
		oDiv = document.createElement('div');
		oDiv.id = this.sID;
		document.body.appendChild( oDiv );
	}
	
	oDiv.style.display = "block";
}

Framework.Blender.prototype.hide = function()
{
	var oDiv = $( this.sID );
	
	if( oDiv )	
		oDiv.style.display = "none";
}

//Framework.Dialog
Framework.Dialog = function()
{
	this.sID = 'FRAMEWORK_DIALOG';
	this.sSourceDivID = "";
	this.oBlender = new Framework.Blender();
}

Framework.Dialog.prototype.show = function( sSourceDivID )
{
	var oDiv = $( this.sID );
	
	if( !oDiv )
	{
		oDiv = document.createElement('div');
		oDiv.id = this.sID;
		document.body.appendChild( oDiv );
	}
		
	var oSourceDiv = $( sSourceDivID );
	
	if( oSourceDiv )
	{
		oDiv.innerHTML = oSourceDiv.innerHTML;
		oSourceDiv.innerHTML = "";
	}
	
	this.sSourceDivID = sSourceDivID;
	
	this.oBlender.show();
	
	oDiv.style.display = "block";
}

Framework.Dialog.prototype.hide = function( sDivID )
{
	var oDiv = $( this.sID );
	
	if( oDiv )
	{
		oDiv.style.display = "none";
		
		var oSourceDiv = $( sSourceDivID );
	
		if( oSourceDiv )
		{
			oSourceDiv.innerHTML = oDiv.innerHTML;
			oDiv.innerHTML = "";
		}
		
		this.oBlender.hide();
	}
}

*/

//Framework.Confirm

Framework.Confirm = function()
{
	this.sDivIDOuter = "Framework.Confirm.Outer";
	this.sDivIDInner = "Framework.Confirm.Inner";
	this.fnHandler = null;
	this.nWidth = 400;
	this.nHeight = 100;
	this.nOpacity = 60;
}

Framework.Confirm.prototype.show = function(sQuestion, fnHandler)
{
	this.sQuestion = sQuestion;
	this.fnHandler = fnHandler;
	
	var oDivOuter = $( this.sDivIDOuter );
	var oDivInner = $( this.sDivIDInner );
	
	if( oDivOuter == null )
	{
		oDivOuter = document.createElement("div");
		
		oDivOuter.id = this.sDivID;
		oDivOuter.style.width = "100%";
		oDivOuter.style.height = "100%";
		oDivOuter.style.position = "absolute";
		oDivOuter.style.zIndex = "80";
		oDivOuter.style.top = 0;
		oDivOuter.style.left = 0;
		oDivOuter.style.verticalAlign = 'middle';
		oDivOuter.style.textAlign = 'center';
		oDivOuter.style.backgroundColor = "White";
		oDivOuter.style['filter'] = "alpha(opacity=" + this.nOpacity + ")";
		oDivOuter.style['-moz-opacity'] = "0." + this.nOpacity; 
		oDivOuter.style['opacity'] = "0." + this.nOpacity; 
		
		var oDivInner = document.createElement('div');
		
		oDivInner.style.width = this.nWidth + 'px';
		oDivInner.style.height = this.nHeight + 'px';
		oDivInner.style.border = "solid 1px Black";
		oDivInner.style.position = "absolute";
		oDivInner.style.left = ( document.body.offsetWidth - this.nWidth  ) / 2 + 'px';
		oDivInner.style.top = ( document.body.offsetHeight - this.nHeight ) / 2 + 'px';
		oDivInner.style.backgroundColor = "#dcdcdc";
		oDivInner.style.zIndex = "81";
		oDivInner.style.padding = '5%';
		oDivInner.style.verticalAlign = 'middle';
		oDivInner.style.textAlign = 'center';
		
		oDivInner.appendChild( document.createTextNode( this.sQuestion ) );
		
		var oBtnCancel = document.createElement("button");
		
		oBtnCancel.appendChild( document.createTextNode("Cancel") );
		oBtnCancel.style.position = "absolute";
		oBtnCancel.style.right = '5px';
		oBtnCancel.style.bottom = '5px';
		oBtnCancel.style.width = '80px';
		
		oDivInner.appendChild( oBtnCancel );
		
		var oBtnOk = document.createElement("button");
		
		oBtnOk.appendChild( document.createTextNode("Ok") );
		oBtnOk.style.position = "absolute";
		oBtnOk.style.right = '90px';
		oBtnOk.style.bottom = '5px';
		oBtnOk.style.width = '80px';
		
		oDivInner.appendChild( oBtnOk );
		
		var oThis = this;
		
		oBtnCancel.onclick = function() 
		{
			oDivOuter.style.display = 'none';
			oDivInner.style.display = 'none';
			
			oThis.fnHandler( 0 );
		}
		
		oBtnOk.onclick = function() 
		{
			oDivOuter.style.display = 'none';
			oDivInner.style.display = 'none';
			
			oThis.fnHandler( 1 );
		}
		
		document.body.appendChild( oDivOuter );
		document.body.appendChild( oDivInner );
	}
	else
	{
		oDivOuter.style.display = "inline";
		oDivInner.style.display = "inline";
	}
}


//Framework.Ajax

Framework.Ajax = function()
{
	this.nAbortTimeout	= 180000;
	this.nTimerID	    = null;
	this.fnCallback     = null;
	this.oRequest       = null;
	this.oDebug		    = new Framework.Debug();
}

Framework.Ajax.prototype.UNINITIALIZED	= 0;
Framework.Ajax.prototype.LOAIND			= 1;
Framework.Ajax.prototype.LOADED			= 2;
Framework.Ajax.prototype.INTERACTIVE	= 3;
Framework.Ajax.prototype.COMPLETE		= 4;

Framework.Ajax.prototype.XMLHttpRequest = function()
{
	var oRequest = null;
	
	try {
		oRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch( e )
	{
		try {
			oRequest = new ActiveXObject("Microsoft.XMLHTTP");
		} 
		catch( e2 )
		{
			if( typeof( XMLHttpRequest ) != "undefined" )
				oRequest = new XMLHttpRequest();
		}
	}
	
	return oRequest;
}

Framework.Ajax.prototype.onStateChange = function()
{	
	if( this.oRequest.readyState == this.COMPLETE )
	{
	    this.oRequest.onreadystatechange = function(){};
		
		try {
		    clearTimeout( this.nTimerID );
		}
		catch( e ) {}
		
		this.nTimerID = 0;
		
		var nStatus = this.oRequest.status || 0;
		var sStatusText = this.oRequest.statusText || "";
		
		this.oDebug.println( nStatus + ' ' + sStatusText );
		this.oDebug.println( this.oRequest.getAllResponseHeaders() + this.oRequest.responseText + '\n');
		
		if( nStatus < 200 || nStatus >= 300 )
	    {
	        if( this.fnCallback )
			    this.fnCallback( new Error("HTTP status: " + nStatus + ' ' + sStatusText) );
			    
			return;
	    }
	    
		var oThis = this;
		
		if( this.fnCallback )
			this.fnCallback( oThis.oRequest );
	}
}

Framework.Ajax.prototype.onTimeout = function()
{
    this.abort();
    
	if( this.fnCallback )
		this.fnCallback( new Error("Connection timed out!") );
}

Framework.Ajax.prototype.abort = function()
{
    if( this.nTimerID )
	{
		clearTimeout( this.nTimerID );
		this.nTimerID = 0;
	}

	try {
        this.oRequest.abort();
    }
    catch( e ) {}
}

Framework.Ajax.prototype.request = function(sUrl, sMethod, fnCallback, sData)
{
	this.oDebug.println("Request");
	this.oDebug.println("URL: " + sUrl);
	this.oDebug.println("Method: " + sMethod);
	this.oDebug.println("Data: " + sData);
	this.oDebug.println("\n");
	
	if( sData && sMethod.toLowerCase() == "get" )
	{
		sUrl += ((sUrl.lastIndexOf('?') != -1 )? '&' : '?') + sData;
		sData = null;
	}
		
	this.fnCallback = fnCallback;
	
	this.oRequest = this.XMLHttpRequest();
	this.oRequest.open(sMethod, sUrl, true);
	this.oRequest.onreadystatechange = this.onStateChange.bind( this );
	this.nTimerID = setTimeout(this.onTimeout.bind( this ), this.nAbortTimeout);
	this.oRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	this.oRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    this.oRequest.setRequestHeader("X-Prototype-Version", Framework.Version);
	this.oRequest.send( sData );
}

Framework.Debug = function()
{
	
}

Framework.Debug.enabled = false;

Framework.Debug.prototype.println = function()
{
	if( !Framework.Debug.enabled )
		return;
		
	if( typeof window.top['__DEBUG_WIN__'] == "undefined" )
	{
		window.top['__DEBUG_WIN__'] = window.open("", "__DEBUG_WIN__", "location=no,menubar=no,resizable=yes,scrollbars=yes,width=" +window.screen.availWidth + ",height=" + window.screen.availHeight);
		window.top['__DEBUG_WIN__'].document.open("text/html", "replace");
		//window.top.focus();
	}
	
	var bHasFault = false;
	
	for(var i=0;i<arguments.length;i++)
	{	
		try {
			if( typeof arguments[i].valueOf != "undefined" )
			{
				window.top['__DEBUG_WIN__'].document.write("<pre>");
				window.top['__DEBUG_WIN__'].document.write( arguments[i].valueOf().replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g, "<br/>") );
				window.top['__DEBUG_WIN__'].document.write("</pre>");
			}
		}
		catch( e )
		{
			if( bHasFault )
				break;
				
			window.top['__DEBUG_WIN__'] = window.open("", "__DEBUG_WIN__", "location=no,menubar=no,resizable=yes,scrollbars=yes,width=" +window.screen.availWidth + ",height=" + window.screen.availHeight);
			window.top['__DEBUG_WIN__'].document.open("text/html", "replace");
			//window.top.focus();
			
			bHasFault = true;
			i--;
		}
	}
	
	window.top['__DEBUG_WIN__'].scrollBy(0, window.top['__DEBUG_WIN__'].document.body.scrollHeight - window.top['__DEBUG_WIN__'].document.body.clientHeight);
	//window.top['__DEBUG_WIN__'].document.close();
	
	//window.top.focus();
}

Framework.Debug.prototype.clear = function()
{
	
}


//Framework.Tooltip
Framework.Tooltip = function( sSourceID, sTargetID  )
{
	this.sSourceID = sSourceID;
	this.sTargetID = sTargetID;
	
	this.initialize();
}

Framework.Tooltip.nDisplayTimeout   = 800;
Framework.Tooltip.nDisappearTimeout = 4000;
Framework.Tooltip.nTimerID			= 0;
Framework.Tooltip.sTargetID			= null;
Framework.Tooltip.oToolDiv			= null;

Framework.Tooltip.prototype.initialize = function()
{
	var oTarget = $( this.sTargetID );
	
	if( !oTarget )
	{
		alert("Element " + this.sTargetID + " not fount!");
		return;
	}
	
	attachEventListener( oTarget, "mouseover", this.mouseover.bind( this ));
	attachEventListener( oTarget, "mouseout" , this.mouseout.bind( this ));
}

Framework.Tooltip.prototype.mouseover = function( event )
{
	event = event || window.event;

	if( !Framework.Tooltip.oToolDiv )
	{
		Framework.Tooltip.oToolDiv = document.createElement("div");
		Framework.Tooltip.oToolDiv.id = 'FRAMEWORK_TOOLTIP';
		document.body.appendChild( Framework.Tooltip.oToolDiv );
	}

	var nPosX = event.pageX || ( event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft );
	var nPosY = event.pageY || ( event.clientY + document.body.scrollTop + document.documentElement.scrollTop );

	Framework.Tooltip.sTargetID = this.sTargetID;

	var oThis = this;

	setTimeout( function()
	{
		if( oThis.sTargetID == Framework.Tooltip.sTargetID )
		{
			Framework.Tooltip.oToolDiv.innerHTML		= $( oThis.sSourceID ).innerHTML;
			Framework.Tooltip.oToolDiv.style.left		= nPosX;
			Framework.Tooltip.oToolDiv.style.top		= nPosY;
			Framework.Tooltip.oToolDiv.style.display	= "block";

			if( Framework.Tooltip.nTimerID )
			{
				try {
					clearTimeout( Framework.Tooltip.nTimerID  );
				}
				catch( e ) {};
			}

			var oThis2 = oThis;
			
			Framework.Tooltip.nTimerID = setTimeout( function()
			{
				if( oThis2.sTargetID == Framework.Tooltip.sTargetID )
				{
					Framework.Tooltip.nTimerID = 0;
					Framework.Tooltip.sTargetID = "";
					Framework.Tooltip.oToolDiv.style.display = "none";
				}

			}, Framework.Tooltip.nDisappearTimeout);
		}
	}, Framework.Tooltip.nDisplayTimeout);
}

Framework.Tooltip.prototype.mouseout = function()
{
	if( this.sTargetID == Framework.Tooltip.sTargetID )
	{
		if( Framework.Tooltip.nTimerID )
		{
			try {
				clearTimeout( Framework.Tooltip.nTimerID  );
			}
			catch( e ) {};
			
			Framework.Tooltip.nTimerID = 0;
		}

		Framework.Tooltip.oToolDiv.style.display = "none";
		Framework.Tooltip.sTargetID = "";
	}
}

