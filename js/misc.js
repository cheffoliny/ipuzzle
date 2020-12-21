
function isDefined( el ) {
	return ( typeof( el ) != 'undefined' );
}

function objFocus( oEl )
{
	if( oEl )
	{
		if( isDefined( oEl.focus ) )
			oEl.focus(); 
						
		if( isDefined( oEl.select ) )
			oEl.select(); 
	}
}

function elFocus( id ) 
{
	var oEl = $( id );
	
	if( oEl )
		objFocus( oEl );
}


function objEnable(oEl, bEnable)
{	
	if( oEl )
	{
		if( isDefined( oEl.disabled ) )
			oEl.disabled = !bEnable;
	}
}

function elEnable(id, bEnable)
{
	var oEl = $( id );
	
	if( oEl )
		objEnable(oEl, bEnable);
}

function objIsEnabled( oEl )
{
	if( oEl )
	{
		if( isDefined( oEl.disabled ) )
			return !oEl.disabled;
	}
		
	return false;	
}

function elIsEnabled( id )
{
	var oEl = $( id );
	
	if( oEl )
		return objIsEnabled( oEl );
	
	return false;
}

function elBlur( id )
{
	var oEl = $( id );
	
	if( oEl )
		objBlur( oEl );
}

function objBlur( oEl )
{
	if( oEl )
	{
		if( isDefined( oEl.blur ) )
			oEl.blur();
	}
}

function objCheck(oEl, bCheck)
{
	if( oEl )
	{
		if( isDefined( oEl.checked ) )
			oEl.checked = bCheck;
	}
}

function elCheck(id, bCheck)
{
	var oEl = $( id );
	
	if( oEl )
		objCheck(oEl, bCheck);
}

function objIsChecked( oEl )
{
	if( oEl )
	{
		if( isDefined( oEl.checked ) )
			return oEl.checked;
	}
	
	return false;
}

function elIsChecked( id )
{
	var oEl = $( id );
	
	if( oEl )
		return objIsChecked( oEl );
	
	return false;
}

function objClear( oEl )
{
	if( oEl )
	{
		if( isDefined( oEl.value ) )
			oEl.value = '';
	}
}

function elClear( id )
{
	var oEl = $( id );
	
	if( oEl )
		objClear( oEl );
}	

function objDisplay(oEl, bDisplay)
{
	if( oEl )
	{
		if( isDefined( oEl.style ) && isDefined( oEl.style.display ) )
			oEl.style.display = bDisplay? 'inline' : 'none';
	}
}

function elDisplay(id, bDisplay)
{
	var oEl = $( id );
	
	if( oEl )
		objDisplay(oEl, bDisplay);
}

function objVisibility(oEl, bVisible)
{
	if( oEl )
	{
		if( isDefined( oEl.style ) && isDefined( oEl.style.visibility ) )
			oEl.style.visibility = bVisible? 'visible' : 'hidden';
	}
}

function elVisibility(id, bVisible)
{
	var oEl = $( id );
	
	if( oEl )
		objVisibility(oEl, bVisible);
}

function objReadOnly(oEl, bReadOnly)
{
	if( oEl )
	{
		if( isDefined( oEl.readOnly ) )
			oEl.readOnly = bReadOnly;
		if( isDefined( oEl.tabIndex ) )
			oEl.tabIndex = ( bReadOnly )? -1 : 0;
	}
}

function elReadOnly(id, bReadOnly)
{
	var oEl = $( id );
	
	if( oEl )
		objReadOnly(oEl, bReadOnly);
}

function objSelectIndex(oEl, nIndex)
{
	if( oEl )
	{
		if(	isDefined( oEl.selectedIndex ) )
			oEl.selectedIndex = nIndex;
	}
}

function elSelectIndex(id, nIndex)
{
	var oEl = $( id );
	
	if( oEl )
		objSelectIndex(oEl, nIndex);
}

function objSetValue(oEl, sValue)
{
	if( oEl )
	{
		if( isDefined( oEl.value ) )
		{
			oEl.value = sValue;
			return true;
		}
	}
	
	return false;
}

function elSetValue(id, sValue)
{
	var oEl = $( id );
	
	if( oEl )
		return objSetValue(oEl, sValue);
	
	return false;
}

function objGetValue( oEl )
{
	if( oEl )
	{
		if( isDefined( oEl.value ) )
			return oEl.value;
	}
	
	return null;
}

function elGetValue( id )
{
	var oEl = $( id );
	
	if( oEl )
		return objGetValue( oEl );
	
	return null;
}

function elGetValueNumber( id )
{
	var nValue = elGetValue( id );
	
	if( nValue != null )
		return parseInt( nValue );
		
	return null;
}

function elGetValueFloat( id )
{
	var nValue = elGetValue( id );
	
	if( nValue != null )
		return parseFloat( nValue );
		
	return null;
}

function objFocusNext( oEl )
{ 	
	if( oEl && oEl.form && oEl.form.elements )
	{
		for (var i=0;i<oEl.form.elements.length;i++)
		{ 
			if(oEl.form.elements[i].name == oEl.name)
			{ 
				while( oEl.form.elements.length > ++i )
				{
					if( isDefined( oEl.isDisabled ) && !oEl.isDisabled )
					{
						objFocus( oEl.form.elements[i] );
						break;
					}
				}
				
				break;
			}
		}
	}
} 

function elFocusNext( id )
{
	var oEl = $( id );
	
	if( oEl )
		objFocusNext( oEl );
}

function select2text(oSelect, sTextID)
{
	var oText = $( sTextID );
	
	if( 
		oText && 
		oText.tagName.toLowerCase() == "input" && 
		oSelect && 
		oSelect.tagName.toLowerCase() == "select" 
		)
	{
		if( oSelect.selectedIndex != -1 && !(oSelect.options.length && oSelect.options[0].value == 0 && oSelect.value == 0))
		{
			if( parseInt( oSelect.value ) )
				oText.value = oSelect.options[ oSelect.selectedIndex ].id;
			else
				oText.value = '';
		}
	}
}

function text2select(oText, sSelID)
{
	var oSelect	= $( sSelID );

	if( 
		oText && 
		oText.tagName.toLowerCase() == "input" && 
		oSelect && 
		oSelect.tagName.toLowerCase() == "select" 
		)
	{
		for(var i=0;i<oSelect.options.length;i++)
		{
			if(oSelect.options[i].id == oText.value)
			{
				if( oSelect.selectedIndex != i )
				{
					oSelect.selectedIndex = i;
					
					if( oSelect.onchange )
						oSelect.onchange();
				}
				
				return true;
			}
		}
		
		if( oSelect.options.length && oSelect.options[0].value == 0 )
		{
			if( oSelect.value != 0  )
			{
				oSelect.value = 0;
			
				if( oSelect.onchange )
					oSelect.onchange();
			}
		}
		else if( oSelect.selectedIndex != -1 )
		{
			oSelect.selectedIndex = -1;		//няма съвпадение!
			
			if( oSelect.onchange )
				oSelect.onchange();
		}
	}
	
	return true;
}

// Поставяне на водещи символи пред стринг
// param: 	sString - стринг, който се форматира
// param: 	sLength - цялата дължина на стринга, заедно с водещите символи, по подразбиране = 8
// param:	sSymbol - водещ символ, по подразбиране  sSymbol = "0";
// result: 	форматиран стринг с водещи символи
function zero_padding (sString, sLength, sSymbol) {
	
	if (!sLength) sLength = 8;
	if (sSymbol) sSymbol = '0';
	
	var numPads 	= sLength-sString.length;
	var leftPad 	= '';;
	
	for (i = 1; i<=numPads; i++)
		leftPad+=sSymbol;
	
	return leftPad+sString;
}

/**
*	Функции за закръгляне след определен брой знака след десетичната точка
*
*	@name roundUP, roundDown, round
*	@author dido2k
*	@param dNumber Числото, което ще се закръгля
*	@param nLength Брой символи след десетичната запетая
*/

function roundUP(dNumber, nLength) {
	return new Number( Math.ceil( parseFloat( dNumber ) * Math.pow(10, nLength ) ) * Math.pow( 10, -nLength ) ).toFixed( nLength );
}

function roundDown(dNumber, nLength) {
	return new Number( Math.floor( parseFloat( dNumber ) * Math.pow(10, nLength ) ) * Math.pow( 10, -nLength ) ).toFixed( nLength );
}

function round(dNumber, nLength) {
	return new Number( Math.round( parseFloat( dNumber ) * Math.pow(10, nLength ) ) * Math.pow( 10, -nLength ) ).toFixed( nLength );
}

function show_hide_loader (func) {
	//Показва панела за зареждане
	loader = $('loading');

    if( !loader ){
        var faIco = document.createElement('i');

        loader = document.createElement("DIV");
        loader.appendChild(faIco);
        loader.id = 'loading';
        loader.style.border = '0px';
        loader.style.textAlign = 'center';
        loader.style.color = 'White';
        loader.style.position = 'absolute';
        loader.style.right = '50%';
        loader.style.bottom = '50%';
        loader.style.zIndex = 1000;
        faIco.className = 'fas fa-puzzle-piece text-primary fa-pulse fa-5x';
        document.body.appendChild(loader);
    }

	if( loader ) loader.style.display = (func == 1) ? 'block' : 'none';
}

/**
*	Функцията предоставя имплементация на DOM swapNode която не е имплементирана от Mozilla/Firefox
*
*	@name swapNode
*	@author dido2k
*	@param node обекта с който текушия обект ще си заменят местата
*/

if( typeof( Node ) != "undefined" )
{
	Node.prototype.swapNode = function( node ) 
	{
	    var nextSibling = this.nextSibling;
	    
	    var parentNode = this.parentNode;
	    
	    node.parentNode.replaceChild(this, node);
	    
	    parentNode.insertBefore(node, nextSibling); 
	 }
}


/**
*	Функциите разширяват класа String като прибавят функционалност за премахване на whitespaces от стринг
*
*	@name lTrim, rTrim, trim
*	@author dido2k
*/

String.prototype.lTrim = function() {
	return this.replace(/^\s+/, "");
}

String.prototype.rTrim = function() {
	return this.replace(/\s+$/, "");
}

String.prototype.trim = function() {
	return this.lTrim().rTrim();
}

/**
*	Един интерфейс, много браузери :))
*
*	@name attachEventListener
*	@author dido2k
*/

function attachEventListener(oElement, sEvent, fnHandler)
{
	if( typeof( window.addEventListener ) != "undefined" )
		oElement.addEventListener(sEvent, fnHandler, false);
	else if( window.attachEvent )
		oElement.attachEvent("on" + sEvent,  fnHandler);
}


/**
*	Проверка за валидност на ЕИН
*
*	@name validateEIN
*	@author dido2k
*/

function validateEIN( sValue )
{
	if( sValue == null || !sValue.length )
		return false;
		
	switch( sValue.length )
	{
		case 9:
		case 13:
			break;
		default:
			return false;
	}	
	
	var nChecksum = 0;
	
	for(var i=0; i<8; i++)
		nChecksum += (i + 1) * parseInt( sValue.charAt( i ) );
	
	nChecksum %= 11;
	
	if( nChecksum != 10 )
	{
		if( parseInt( sValue.charAt( 8 ) ) != nChecksum )
			return false;
	}
	else
	{
		nChecksum = 0;
	
		for(var i=0; i<8; i++)
			nChecksum += (i + 3) * parseInt( sValue.charAt( i ) );
			
		nChecksum %= 11;
		
		if( nChecksum != 10 )
		{
			if( parseInt( sValue.charAt( 8 ) ) != nChecksum )
				return false;
		}
		else
		{
			if( parseInt( sValue.charAt( 8 ) ) != 0 )
				return false;
		}
	}
	
	if( sValue.length == 13 )
	{
		var aMultiplier = new Array(2, 7, 3, 5);
		
		nChecksum = 0;
		
		for(var i=0; i<aMultiplier.length; i++)
			nChecksum += aMultiplier[ i ] * parseInt( sValue.charAt( i + 8 ) );
			
		nChecksum %= 11;
		
		if( nChecksum != 10 )
		{
			if( parseInt( sValue.charAt( 12 ) ) != nChecksum )
				return false;
		}
		else
		{
			aMultiplier = new Array(4, 9, 5, 7);
	
			nChecksum = 0;
			
			for(var i=0; i<aMultiplier.length; i++)
				nChecksum += aMultiplier[ i ] * parseInt( sValue.charAt( i + 8 ) );
				
			nChecksum %= 11;
			
			if( nChecksum != 10 )
			{
				if( parseInt( sValue.charAt( 12 ) ) != nChecksum )
					return false;
			}
			else
			{
				if( parseInt( sValue.charAt( 12 ) ) != 0 )
					return false;
			}
		}
	}
	
	return true;
}

/**
*	Проверка за валидност на ЕИН по ДДС
*	
*	@name	validateEinDDS
*	@autor	stanislav
*/

function validateEinDDS(sValue) {

//	for(var i=0;i<sValue.length;i++) {
//		var letter = sValue.charAt(i);
//		if( letter >= '0' && letter <= '9' ) {
//			var sNumbersPart = sValue.substr(i);
//			return validateEIN(sNumbersPart);
//		}
//	}
//	return false;
	
	// Pavel
	var s = trim(sValue);
	var len = s.length;
	var ein = '';
	
	if ( len < 11 ) {
		return false;
	}
	
	for ( i = 0; i < len; i++ ) {
		var ch = s.charAt(i);
		
		if ( !isNaN( parseInt(ch) ) ) {
			ein += ch;
			
			if ( validateEIN(ein) ) {
				return true;
			}
		}
	}

	return false;
}

function checkAll( bChecked ) {
	var aCheckboxes = document.getElementsByTagName('input');
	
	for( var i=0; i<aCheckboxes.length; i++ ) {
		if ( aCheckboxes[i].type.toLowerCase() == 'checkbox' ) {

			if ( aCheckboxes[i].parentNode.getAttribute('disabled') !== 'undefined' && aCheckboxes[i].parentNode.getAttribute('disabled') !== null && aCheckboxes[i].parentNode.getAttribute('disabled') !== false) {
				aCheckboxes[i].checked = false;
				aCheckboxes[i].disabled = true;
				aCheckboxes[i].parentNode.style.visibility = 'visible';
			} else {
				aCheckboxes[i].checked = bChecked;
				aCheckboxes[i].disabled = false;
			}
		}
	}
}

/**
*	Проверка за валидност на ЕГН
*	
*	@name	validateEGN
*	@autor	Pavel
*/
function validateEGN(s) {
    var t = [2,4,8,5,10,9,7,3,6];
    
    if ( typeof s != 'string' ) {
    	return false;
    }
    
    if ( s.length != 10 ) {
    	return false;
    }
    
    var rv; 
    var rr = 0;
    
    for ( var i = 0; i < 9; i++ ) {
        if ( s.charAt(i) == 0 ) {
        	continue;
        }
        
        rr = rr + ( s.charAt(i) * t[i] );
    }
    
    var chs = 0;
    
    chs = (rr % 11);
    
    if ( chs == 10 ) {
    	chs = 0;
    }
    
    if ( s.charAt(9) == chs ) {
    	return true;
    } else {
    	return false;
    }
}

/**
*	Проверка за наличие на стойност (изключвайки '0') в текстово поле
*	
*	@name	isEmpty
*	@autor	Pavel
*/
function isEmpty(aText) {
	if ( (aText.length == 0) || (aText == null) || (aText == '0') ) {
		return true;
	} else { 
		return false; 
	}
}	

/**
*	Изчиства "белите пространства" в зададения текст 
*	
*	@name	trim
*	@autor	Pavel
*/
function trim(str) {
	return str.replace(/^\s+|\s+$/g,"");
}

function unserialize( data )
{ 
	try
	{
		var error = function( type, msg, filename, line ) {throw new window[type] ( msg, filename, line );};  
		var read_until = function( data, offset, stopchr )
		{
			var buf = [];
			var chr = data.slice( offset, offset + 1 );
			var i = 2;
			while( chr != stopchr )
			{
				if( ( i + offset ) > data.length ) {error( 'Error', 'Invalid' );}
				buf.push( chr );
				chr = data.slice( offset + ( i - 1 ),offset + i );
				i += 1;
			}
			return [buf.length, buf.join( '' )];  
		};
		
		var read_chrs = function( data, offset, length )
		{  
			buf = [];
			for( var i = 0; i < length; i++ )
			{
				var chr = data.slice( offset + ( i - 1 ), offset + i );
				buf.push( chr );
			}
			
			return [buf.length, buf.join( '' )];
		};
		
		var _unserialize = function ( data, offset )
		{
			if( !offset ) offset = 0;
			var buf = [];
			var dtype = ( data.slice( offset, offset + 1 ) ).toLowerCase();
			
			var dataoffset = offset + 2;
			var typeconvert = new Function( 'x', 'return x' );
			var chrs = 0;
			var datalength = 0;
			
			switch( dtype )
			{
				case "i":
					typeconvert = new Function( 'x', 'return parseInt( x )' );
					var readData = read_until( data, dataoffset, ';' );
					var chrs = readData[0];
					var readdata = readData[1];
					dataoffset += chrs + 1;
					break;
				case "b":
					typeconvert = new Function( 'x', 'return ( parseInt( x ) == 1 )' );
					var readData = read_until( data, dataoffset, ';' );
					var chrs = readData[0];
					var readdata = readData[1];
					dataoffset += chrs + 1;
					break;
				case "d":
					typeconvert = new Function( 'x', 'return parseFloat( x )' );
					var readData = read_until( data, dataoffset, ';' );
					var chrs = readData[0];
					var readdata = readData[1];
					dataoffset += chrs + 1;
					break;
				case "n":
					readdata = null;
					break;
				case "s":
					var ccount = read_until( data, dataoffset, ':' );
					var chrs = ccount[0];
					var stringlength = ccount[1];
					dataoffset += chrs + 2;
				
					var readData = read_chrs( data, dataoffset + 1, parseInt( stringlength ) );
					var chrs = readData[0];
					var readdata = readData[1];
					dataoffset += chrs + 2;
					if( chrs != parseInt( stringlength ) && chrs != readdata.length ) {error( 'SyntaxError', 'String length mismatch' );}
					break;
				case "a":
					var readdata = {};
					
					var keyandchrs = read_until( data, dataoffset, ':' );
					var chrs = keyandchrs[0];
					var keys = keyandchrs[1];
					dataoffset += chrs + 2;
					
					for( var i = 0; i < parseInt( keys ); i++ )
					{
						var kprops = _unserialize( data, dataoffset );
						var kchrs = kprops[1];
						var key = kprops[2];
						dataoffset += kchrs;
						
						var vprops = _unserialize( data, dataoffset );
						var vchrs = vprops[1];
						var value = vprops[2];
						dataoffset += vchrs;
						
						readdata[key] = value;
					}
					
					dataoffset += 1;
					break;
				default:
					error( 'SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype );
					break;
			}
			
			return [dtype, dataoffset - offset, typeconvert( readdata )];
		};
		
		return _unserialize( data, 0 )[2];
	}
	catch( err ) {}
}

function empty(p) {return p==0 || !p || (typeof(p) == 'object' && jQuery.isEmptyObject(p));}

function in_array( needle, haystack )
{
	var key = '';
	for( key in haystack ) if( haystack[key] == needle ) return true;
	return false;
}

function validateEmail(s) {
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;	
	if (reg.test(s) == false) return false;
	return true;
}

/**
*	Проверка за задължителни полета. Елементи съдържащи EGN, EMAIL в ID-то си се валидират и за коректни стойности
*	Невалидните полета се сетват с червен бордер css/sales.css -> invalidFields 
*	@name	validateRequired
*	@autor	Ivo
*	@param aFields масив с DOM елементите
*/
function validateRequired(aFields) {
	var bHasInvalids = 0;
	for (var i=0;i<aFields.length;i++) {
		if ( empty(aFields[i].value) || 
			(aFields[i].id.search("EGN")>-1   && !validateEGN(aFields[i].value)) ||
			(aFields[i].id.search("EMAIL")>-1 && !validateEmail(aFields[i].value))
	       ) 
		{
			addClass(aFields[i],"invalidField"); 
			bHasInvalids = -1;
		} else {
			removeClass(aFields[i],"invalidField"); 			
		}
	}
	return bHasInvalids + 1;
}	

function hasClass(ele,cls) {
	return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}

function addClass(ele,cls) {
	if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}

function removeClass(ele,cls) {
	if (hasClass(ele,cls)) {
		var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
		ele.className=ele.className.replace(reg,' ');
	}	
}

function getElementsByClassName(parent,className){
	var nodes = [];
	var sClass = new RegExp('\\b'+className+'\\b');
	var elements = parent.getElementsByTagName('*');
	for (var i=0;i<elements.length;i++)	(sClass.test(elements[i].className)) && nodes.push(elements[i]);	
	return nodes;
}

function getRadioValue(elName) {
	var aEl = document.getElementsByName(elName);
	if (empty(aEl)) return false;
	for (var i=0;i<aEl.length;i++) if (aEl[i].checked) return aEl[i].value;			
	return 0;
}

function validateLK(s) {
	var t = [2,4,8,5,10,9,7,3];			 
   
    if ( typeof s != 'string' ) {
    	return false;
    }
    
    if ( s.length != 9 ) {
    	return false;
    }

	return true;
// za nowite documenti cheksumata e na gyrba
// tegla [7,3,1,7,3,1,7,3,1]
// algorityma e syshtiq samo che delim na modul ot 10
	
	
//    var rv; 
//    var rr = 0;
//    
//    for ( var i = 0; i < 8; i++ ) {
//        if ( s.charAt(i) == 0 ) {
//        	continue;
//        }
//        
//        rr = rr + ( s.charAt(i) * t[i] );
//    }
//    
//    var chs = 0;
//    
//    chs = (rr % 11);
//    
//    if ( chs == 10 ) {
//    	chs = 0;
//    }
//    
//    if ( s.charAt(8) == chs ) {
//    	return true;
//    } else {
//    	return false;
//    }
}

function alertSystem(message) {
	
	var window_width = jQuery(window).width();
	
	var	left_value = (window_width-280) / 2;
	
	jQuery('#systemMessageBG').css('display','block');	
	jQuery('#systemMessageDialog').css('display','block');	
	jQuery('#systemMessageDialog').css('left',left_value);	
	jQuery('#sytemMessageValue').html(message);
			
}
function closeSystemMessage(message) {	
	jQuery('#systemMessageBG').css('display','none');					
	jQuery('#systemMessageDialog').css('display','none');					
}

function showLoader() {
	//Показва панела за зареждане
	var _rpc_obj_loader2 = document.getElementById('loading2');

	if( !_rpc_obj_loader2 ){
		_rpc_obj_loader2 = document.createElement("DIV");
		_rpc_obj_loader2.appendChild(document.createTextNode('Зареждане ...'));
		_rpc_obj_loader2.id = 'loading2';
		_rpc_obj_loader2.style.background = '#39b3d7';
		_rpc_obj_loader2.style.border = '1px solid #ffffff';
		_rpc_obj_loader2.style.textAlign = 'center';
		_rpc_obj_loader2.style.color = 'White';
		_rpc_obj_loader2.style.position = 'absolute';
		_rpc_obj_loader2.style.width = 220;
		_rpc_obj_loader2.style.height = 18;
		_rpc_obj_loader2.style.bottom = 2;
		_rpc_obj_loader2.style.right = 2;
		_rpc_obj_loader2.style.zIndex = 1000;
		document.body.appendChild(_rpc_obj_loader2);				
	}
	if( _rpc_obj_loader2 ) _rpc_obj_loader2.style.display = 'block';
}

function closeLoader() {
	var _rpc_obj_loader2 = document.getElementById('loading2');
	// Скриване на панела за зареждане
	if(_rpc_obj_loader2) _rpc_obj_loader2.style.display = 'none';
}

function countProperties(obj) {
    var count = 0;

    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
                ++count;
    }

    return count;
}

function loadJSON(api_action,aData,onSuccess) {

    var aRequestData = {};
    aRequestData['action_script'] = rpc_action_script;
    aRequestData['api_action'] = api_action;
    aRequestData['rpc_version'] = 2;

    for(i in aData) {
        if (!aData.hasOwnProperty(i)) continue;
        aRequestData[i] = aData[i];
    }

    showLoader();

    jQuery.ajax({
        url: "api/api_general.php",
        dataType: 'json',
        data: aRequestData,
        type: 'POST',
        success: function(response_data){
            closeLoader();

            onSuccess(response_data);
        },
        error: function(err) {
            closeLoader();
            alert('connection error');
        }
    });
}

intval = function(i) {
	return parseInt(i,10) || 0;
}