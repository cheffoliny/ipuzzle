function onFormKeyNum( event )
{	
    var keyCode = document.all? event.keyCode : event.which;
    
    var dRes = ( keyCode >= 0x30 && keyCode <= 0x39 );
    
    event.returnValue = dRes;
    
    return dRes;
}

/// HTTPResponser /////
//
var _submit_form = ''; // forma koqto shte se submitva

var req 			= false;
var loader_loaded 	= false;

var target_area = '';
var requested_url = false;

function loadXMLDoc( url, target, requestor ) {
	req 			= false;
	target_area 	= target;
    requested_url 	= url;
    
    
    if(window.XMLHttpRequest) {
    	try {
			req = new XMLHttpRequest();
        } catch(e) {
			req = false;
        }
   
    } else if(window.ActiveXObject) {
       	try {
        	req = new ActiveXObject("Msxml2.XMLHTTP");
      	} catch(e) {
        	try {
          		req = new ActiveXObject("Microsoft.XMLHTTP");
        	} catch(e) {
          		req = false;
        	}
		}
    }
	if(req) {
		
		var aParams = new Array();
		
		aParams = url.split('form=');
		
		if( typeof aParams[1] == 'string' )
			_submit_form = aParams[1];
			
		
		req.onreadystatechange = processReqChange;
		
			url += "&form=" + _submit_form;
		
		//alert( url );
		//alert( target );
		
		var params = form2POST( _submit_form );
		
		_submit_form = '';
		
		if( params.length > 0 )
		{
			url += "&" + params;
			params = '';
		}		
		
		//alert( url );
		req.open("GET", url, true);
		req.send("send");
		
		
		//menu
//		if( requestor )
//			activate( requestor );
	}else{
		
		alert('Error.');
	}
}


//
function processReqChange() {

	if( document.getElementById( target_area ) && loader_loaded == false )
	{
		document.getElementById( target_area ).innerHTML = '&nbsp;';
		document.getElementById( target_area ).style.backgroundImage = "url('images/loading.gif')";
		document.getElementById( target_area ).style.backgroundRepeat = 'no-repeat';
		document.getElementById( target_area ).style.backgroundPosition = 'center';
		loader_loaded = true;
	}
    // only if req shows "loaded"
    if (req.readyState == 4)
    {
        // only if "OK"
        
        if (req.status == 200)
        {
        	document.getElementById( target_area ).style.backgroundImage = 'none';
        	document.getElementById( target_area ).innerHTML = req.responseText;
        	loader_loaded = false;
        	
        } else {
     
        	if( document.getElementById( target_area ) )
        	{
	        	document.getElementById( target_area ).style.backgroundImage = 'url(images/error.png)';
	        	document.getElementById( target_area ).style.backgroundRepeat = 'no-repeat';
				document.getElementById( target_area ).style.backgroundPosition = 'center';
        	}
        	
        }
        
    }
    
}



function form2POST(rpc_form) {
	
		str = '';

		if( form=document.getElementById(rpc_form) ) {
			for(i=0;i<form.elements.length;i++) {
				
				// Запомня атрибута disabled на всички елементи на формата
				if(form.elements[i].name != '' && form.elements[i].name != 'undefined') {
					if(form.elements[i].tagName == 'SELECT') {
						for(j=0;j<form.elements[i].options.length;j++)
						if(form.elements[i].options[j].selected == true) {
							str += (str == '' ? "" : "&") + form.elements[i].name + '=' + encodeURIComponent(form.elements[i].options[j].value);
						}
						continue;
					}
					if(form.elements[i].tagName == 'INPUT' && form.elements[i].type.toUpperCase() == 'RADIO') {
						if(form.elements[i].checked == true)
						str += (str == '' ? "" : "&") + form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value);
						continue;
					}
					if(form.elements[i].tagName == 'INPUT' && form.elements[i].type.toUpperCase() == 'CHECKBOX') {
						str += (str == '' ? "" : "&") + form.elements[i].name + '=' + encodeURIComponent(form.elements[i].checked == true ? 1 : 0);
						continue;
					}
					if (form.elements[i].tagName == 'TEXTAREA') {
						str += (str == '' ? "" : "&") + form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value);
						continue;
					}

				    if (form.elements[i].tagName == 'INPUT') str += (str == '' ? "" : "&") + form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value);

                }

            }
		}
        return str;

}
	
	function submitForm( id , url, target, requestor ) {
		_submit_form = id;
		loadXMLDoc( url, target, requestor );
		return false;
	}

function checkReason() {
    var reason = document.forms['details']['alarm_reason'].value;
	var reason2 = document.forms['details']['alarm_reason2'].value;
    if ( ( reason == null || reason == "" || reason == 0 ) && ( reason2 == null || reason2 == "" || reason2 == 0 ) ) {
        alert("НЕ СТЕ ПОСОЧИЛИ ПРИЧИНА!");
        return false;
    }
}