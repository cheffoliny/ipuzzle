
Performance = function()
{
	this.initialize();
}

Performance.INTERVAL = 0;
Performance.ENABLED = false;

Performance.getInstance = function()
{
    if( typeof( window.top['__PERFORMANCE__'] ) == "undefined" )
		window.top['__PERFORMANCE__'] = new Performance();
		
	return window.top['__PERFORMANCE__'];
}

Performance.attachEventListener = function(oElement, sEvent, fnHandler)
{
	if( typeof( window.addEventListener ) != "undefined" )
		oElement.addEventListener(sEvent, fnHandler, false);
	else if( window.attachEvent )
		oElement.attachEvent("on" + sEvent,  fnHandler);
}

Performance.prototype.initialize = function()
{
	this.nStartTime     = 0;
	this.nEndTime       = 0;
	this.nQueryTime     = 0;
	this.oDebug		    = new Framework.Debug();
	this.oAjax          = new Framework.Ajax();
    this.nTimeoutID     = 0;
    this.oIndicator     = new Performance.Indicator();
	
	if( Performance.ENABLED )
        this.nTimeoutID = setTimeout( this.request.bind( this ), 0 );
}

Performance.prototype.request = function()
{
    this.oAjax.request("api/api_performance.php", "get", this.onResponse.bind( this ), this.serialize());
}

Performance.prototype.onResponse = function( oResponse )
{	
    try {
        if( oResponse instanceof Error ) 
            throw oResponse;
        
        eval("var oObject = " + oResponse.responseText + ';' );
        
        if( typeof( oObject['error'] ) != "undefined" )
            throw new Error( oObject['error'] );
            
        this.nStartTime = oObject['st'];
        this.nQueryTime = oObject['qt'];
        this.nEndTime = ( new Date() ).getTime();
        
        if( typeof( oObject['pn'] ) != "undefined" )
            this.oIndicator.setNet( oObject['pn'] );
        
        if( typeof( oObject['pq'] ) != "undefined" )
            this.oIndicator.setSql( oObject['pq'] );
    }
    catch( e )
    {   
        this.oDebug.println("Error:" + e.message);
        
        this.nStartTime     = 0;
	    this.nEndTime       = 0;
	    this.nQueryTime     = 0;
	    
	    this.oIndicator.setUnknown();
    }
    
    this.nTimeoutID = setTimeout( this.request.bind( this ), Performance.INTERVAL );
}

Performance.prototype.serialize = function()
{
	var aPairs = new Array();
	
	aPairs.push( encodeURIComponent("st") + '=' + encodeURIComponent( ( new Date() ).getTime() ) );
	
	if( this.nStartTime )
		aPairs.push( encodeURIComponent("lst") + '=' + encodeURIComponent( this.nStartTime ) );
		
	if( this.nEndTime )
		aPairs.push( encodeURIComponent("let") + '=' + encodeURIComponent( this.nEndTime ) );
		
	if( this.nQueryTime )
		aPairs.push( encodeURIComponent("lqt") + '=' + encodeURIComponent( this.nQueryTime ) );
		
	return aPairs.join('&');
}

Performance.Indicator = function()
{
    this.sDivID = "PERFORMANCE";
    this.sSqlID = "PERFORMANCE_INDICATOR_SQL";
    this.sNetID = "PERFORMANCE_INDICATOR_NET";
    
    this.sSrcUnknown = "images/monitoring-x.gif";
    this.sTitleUnknown = "N\\A";
    
    this.oDiv       = null;
    this.oImgSQL    = null;
    this.oImgNET    = null;
    
    this.initialize();
}

Performance.Indicator.prototype.initialize = function()
{
    this.oDiv = window.top.document.createElement("div");
    this.oDiv.id = this.sDivID;
    
    this.oImgSQL = window.top.document.createElement("img");
    this.oImgNET = window.top.document.createElement("img");
    
    this.oImgSQL.id = this.sSqlID;
    this.oImgNET.id = this.sNetID;
    
    this.oDiv.appendChild( this.oImgSQL );
    this.oDiv.appendChild( this.oImgNET );
    
    this.setUnknown();
    
    window.top.document.body.appendChild( this.oDiv );
}

Performance.Indicator.prototype.percentToLevel = function( nPercent )
{
	return ( Math.floor( Math.min( parseInt( nPercent ), 179 ) / 20 ) + 1 );
}

Performance.Indicator.prototype.setNet = function( nPercent )
{
    this.oImgNET.src = "images/monitoring-" + this.percentToLevel( nPercent ) + ".gif";
    this.oImgNET.title = "Internet: " + nPercent + '%';
}

Performance.Indicator.prototype.setSql = function( nPercent )
{
    this.oImgSQL.src = "images/monitoring-" + this.percentToLevel( nPercent ) + ".gif";
    this.oImgSQL.title = "EOL: " + nPercent + '%';
}

Performance.Indicator.prototype.setUnknown = function()
{
    this.oImgSQL.title = this.sTitleUnknown;
    this.oImgNET.title = this.sTitleUnknown;
    
    this.oImgSQL.src = this.sSrcUnknown;
    this.oImgNET.src = this.sSrcUnknown;
}

Performance.attachEventListener( window, "load", Performance.getInstance );