var dragdiv = null;
var dragx 	= 0;
var dragy 	= 0;
var posx 	= 0;
var posy 	= 0;

function draginit() {
	document.onmousemove 	= drag;
	document.onmouseup 		= dragstop;
}

function dragstart(element) {
	dragdiv = element;
	dragx 	= posx - dragdiv.offsetLeft;
	dragy 	= posy - dragdiv.offsetTop;
}

function dragstop() {
	dragdiv	= null;
}

function drag(ev) {
	posx = document.all ? window.event.clientX : ev.pageX;
	posy = document.all ? window.event.clientY : ev.pageY;
	
	if(dragdiv != null) {
		dragdiv.style.left 	= (posx - dragx) + "px";
		dragdiv.style.top 	= (posy - dragy) + "px";
	}
}

function show_hide_div (div_name, show_hide_param) {
	var oDiv = document.getElementById(div_name);
	
	
	show_background_div (show_hide_param);	
	
	if (show_hide_param!='none') { 
		oDiv.style.zIndex 	= "49"; 
		oDiv.style.border 	= "0.1mm solid black";
		oDiv.style.background 	= "#f4f8ff";
		oDiv.style.position	= "absolute";
		oDiv.style.filter  	= "progid:DXImageTransform.Microsoft.dropshadow(OffX=5, OffY=5, Color='#cccccc, Positive='true')";
		oDiv.style.top 		= (document.body.offsetHeight - parseInt(oDiv.style.height)) / 2;
    	oDiv.style.left 	= (document.body.offsetWidth  - parseInt(oDiv.style.width)) / 2;
		oDiv.style.display 	= 'inline';   	
	} else 
		oDiv.style.display 	= 'none';
	
	return true;
}

function show_background_div (show_hide_param) {
	
	var opacity = 30;
	var oBgIframe = document.getElementById("@bgiframe@");
	
	if( !oBgIframe ){
		oBgIframe 					= document.createElement("<div>");
		oBgIframe.id 				= "@bgiframe@";
		oBgIframe.style.background 	= "white";
		oBgIframe.style.position	= "absolute";
		oBgIframe.style.width 		= document.body.offsetWidth;
		oBgIframe.style.height 		= document.body.offsetHeight;
		oBgIframe.style.top 		= 0;
		oBgIframe.style.left 		= 0;
		oBgIframe.style.zIndex 		= 48;
		oBgIframe.style['filter']	= "alpha(opacity="+opacity+")";
		oBgIframe.style['-moz-opacity'] = "0."+opacity; 
		oBgIframe.style['opacity'] 		= "0."+opacity; 
				
		document.body.appendChild(oBgIframe);				
	}
	if( oBgIframe ) oBgIframe.style.display = show_hide_param;	
	
}
