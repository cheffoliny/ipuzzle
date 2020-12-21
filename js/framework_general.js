	/**
	 *	vPopUp({
	 *  		url: URL,
	 *  		name: WINDOW_NAME,
	 *  		width: ...,
	 *  		height: ...,
	 *  		reload: true, <- ако искате да презаредите попъпа при фокус на прозореца
	 *	})
	 */
	function vPopUp(params) {
		const { url, name, width = 300, height = 300, reload = false, offsetY = 30, offsetX = 8.5 } = params
		const urlPlaceholder = reload ? url : "";
		const offsetLeft = window.screen.availLeft !== 0
			? (window.screen.availWidth - width) / 2 + window.screen.availLeft - offsetX
			: (window.screen.availWidth - width) / 2 - window.screen.availLeft - offsetX
		const offsetTop = window.screen.availTop !== 0
			? (window.screen.availHeight - height) / 2 + window.screen.availTop - offsetY
			: (window.screen.availHeight - height) / 2 - window.screen.availTop - offsetY
		const newWindow = window.open(urlPlaceholder, name, `width=${width}, height=${height}, left=${offsetLeft}, top=${offsetTop}`);

		if (newWindow.location == "about:blank") {
			//quirks scaled screens o_O fuuuu drug put
			if (window.devicePixelRatio !== 1) {
				const offsetTopCorrection = window.screen.availTop !== 0 ? 30 : -30
				/*
				?  (((window.screen.availHeight - height) / 2) * window.devicePixelRatio) + (window.screen.availTop * window.devicePixelRatio) - offsetY
				:  (window.screen.availHeight - height) / 2 - (window.screen.availTop * window.devicePixelRatio) - offsetY
				*/
				newWindow.moveTo(offsetLeft, offsetTop + offsetTopCorrection)
			}
			newWindow.location.href = url;
		}

		newWindow.focus();
	}

	/*
	 * function:	dialog_win
	 * description:	отваря попъ прозорец
	 * author:		plamen (2005-09-16)
	 * parameters :
	 *				filter: кореспондиращ скрипт -  'page.php?page='+filter				
	 *				w: височина на прозореца			
	 *				h: ширина на прозореца
	 *				scroll: 1 - показва скролери; 0 - скрива скролери				
	 *				winname: име на прозореца				
	 *				resize: 1 - на прозореца може да се променят размерите				
	 */
	
	 function dialog_win(filter, w, h, scroll, winname, resize) {
		var _filter = filter == "undefined" ? 0 : filter;
		var _w = w == undefined ? 600 : w;
		var _h = h == undefined ? 400 : h;
		var _scroll = scroll == undefined ? 0 : scroll;
		var _winname = winname == undefined ? "" : winname;
		var _resize = resize == undefined ? 0 : resize;
		
		var xMax = 800;
		var yMax = 600;

		if (document.all){ 
			xMax = screen.width;
			yMax = screen.height;
		} else {
			xMax = window.outerWidth;
			yMax = window.outerHeight;
		}

		var xOffset = (xMax - _w)/2;
		var yOffset = (yMax - _h)/2;
		if (yOffset > 30) yOffset = yOffset - 30; else yOffset = 0;

		resolveit = 'width=' + _w + ', height=' + _h + ', directories=0, hotkeys=0, location=0, menubar=0, resizable='+ _resize +', screenX='+ xOffset +', screenY=' + yOffset +', scrollbars=' + _scroll +', status=0, toolbar=0, left=' + xOffset +', top=' + yOffset;
		var nWin = window.open('page.php?page='+filter, _winname, resolveit);
		nWin.focus();
		
		return nWin;
	}
	
	/*
	function dialog_win(filter, w, h, scroll, winname, resize)
 	{
 		var _filter = filter == "undefined" ? 0 : filter;
		var _w = w == undefined ? 600 : w;
		var _h = h == undefined ? 400 : h;
		var _scroll = scroll == undefined ? 0 : scroll;
		var _winname = winname == undefined ? "" : winname;
		var _resize = resize == undefined ? 0 : resize;
		
		var xMax = 800;
		var yMax = 600;

		if (document.all){ 
			xMax = screen.width;
			yMax = screen.height;
		} else {
			xMax = window.outerWidth;
			yMax = window.outerHeight;
		}

		yMax -= 150;
		
		var xOffset = (xMax - _w)/2;
		var yOffset = (yMax - _h)/2;
		
		var oIFrame = $('modalDialog');	
		
		oIFrame.style.display = "block";
		
		var oIFrameInner = $('modalDialogInner');
		
		oIFrameInner.style.left  	= xOffset;
 		oIFrameInner.style.top   	= yOffset;
 		oIFrameInner.style.width 	= _w;
 		oIFrameInner.style.height  	= _h;
 		oIFrameInner.style.display	= "block";
 		
 		oIFrameInner.name = winname;
 		oIFrameInner.src = 'page.php?page=' + filter;
 	}
 	*/
	
	function focus_element(obj_id)	
	{
		if(obj=document.getElementById(obj_id)){
			obj.focus();
			obj.select();
		}
	}	
	
	function Resizer(div,footer)
	{
		if ( div ) {
			var footerHeight = footer ? footer.offsetHeight : 0;
			var winHeight = isIE ? document.body.offsetHeight : window.innerHeight; 

			var div_height=winHeight - div.offsetTop - footerHeight;
			div.style.height = div_height>30 ? div_height : 30; 
		}
		return true;
	}
	
	function key_num_dot(e)	
	{

		var moz = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined');
		
		if (moz)
		{	
				
			if(((e.charCode > 45 && e.charCode < 47) || (e.charCode > 47 && e.charCode < 58)) || e.charCode == 0){ return true;}
			else
			{		
					
				e.returnValue = false;
				return false;
			}
		}
		else
		{
			
			if((event.keyCode > 45 && event.keyCode < 47)|| (event.keyCode > 47 && event.keyCode < 58)){ return true;}
			else 
			{
				event.returnValue = false;
				return false;
			}
		}		
	}
	
	function key_num_slash(e)	
	{
		var moz = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined');
		
		if (moz)
		{			
			if(((e.charCode > 45 && e.charCode < 48) || (e.charCode > 47 && e.charCode < 58)) || e.charCode == 0){ return true;}
			else
			{				
				e.returnValue = 158;
				return false;
			}
		}
		else
		{
			if((event.keyCode > 45 && event.keyCode < 48)|| (event.keyCode > 47 && event.keyCode < 58)){ return true;}
			else 
			{
				event.returnValue = 158;
				return false;
			}
		}		
	}


	function key_num( e )	{
		var moz = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined');
		
		if (moz)
		{			
			if(((e.charCode > 47 && e.charCode < 58)) || e.charCode == 0){ return true;}
			else
			{				
				e.returnValue = false;
				return false;
			}
		}
		else
		{
			if(event.keyCode > 47 && event.keyCode < 58){ return true;}
			else 
			{
				event.returnValue = false;
				return false;
			}
		}		
	}


	function num_by_select(num_id, sel_id, sel_type)
	{
		oNum = document.getElementById(num_id);
		oSel = document.getElementById(sel_id);
		if (sel_type == 'couriers')
		{
			if( oSel.selectedIndex >=0 )
				oNum.value = oSel.options[oSel.selectedIndex].value;
		}
		else
		{
			if( oSel.selectedIndex >=0 )
				oNum.value = oSel.options[oSel.selectedIndex].id;
		}
	}

	function select_by_num(num_id, sel_id, sel_type)
	{
		oNum = document.getElementById(num_id);
		oSel = document.getElementById(sel_id);
		
		for (i=0; i < oSel.length; i++)
		{
			if (sel_type == 'couriers')
			{
				if (oSel.options[i].value == oNum.value  && oSel.selectedIndex != i)
				{
					oSel.selectedIndex = i;
					oSel.onchange();
					return true;
					break;
				}
			}
			else
			{
				if (oSel.options[i].id == oNum.value && oSel.selectedIndex != i)
				{
					oSel.selectedIndex = i;
					oSel.onchange();
					return true;
					break;
				}
			}
		}		
		return false;
	}
	
	
	function move_option_to (lid, rid, direction) {
		clear_flag = false;
		if (direction == 'right') {
			sid = lid;
			id = rid;
		}
		else {
			sid = rid;
			id = lid;
		}
		obj = document.getElementById(sid);
		if(obj.options.length && obj.selectedIndex != -1) {
			while(obj.selectedIndex != -1) {
				OPT = obj.options[obj.selectedIndex];
				vSEL = document.getElementById(id);
				nOPT = document.createElement('OPTION');
				nOPT.value = OPT.value;
				nOPT.text = OPT.text;
				for(i=0;i<vSEL.options.length;i++) if(vSEL.options[i].text > nOPT.text && vSEL.options[i].value != '') break
				if (OPT.value == '') {
					i = 0;
					clear_flag = true;
				}
				vSEL.add(nOPT, (isIE) ? i : vSEL.options[i]);
				obj.remove(obj.selectedIndex);
			}
	
			if(direction == 'right' && clear_flag) {
				while(vSEL.options.length > 1)  
					vSEL.remove(1);

				obj.disabled = true;
			}
	
			if(direction == 'left' && clear_flag) {
				vSEL.disabled = false;
			}
		}
	}
		
	function select_all_options (id) {
		
		oSEL = document.getElementById(id);
		if (oSEL.options.length) {
			for(i=0;i<oSEL.options.length;i++) oSEL.options[i].selected = true;
			return true;
		}
		else return false;
	}

	function printFiskal()
	{
		
		if( oFeiskalContent = $('print_fiskal') )
		{
			aElements = oFeiskalContent.value.split("@@");
			for (i=0; i < aElements.length; i++)
			{
				aElement = 	aElements[i].split("##");
				
				if( aElement[0].trim().length != 0 )
					SaveFile( aElement[0]+'.BON', aElement[1], '');
			}
		}
		
		rpc_on_exit = function(){};
	}
	
	function SaveFile( sFileName, sContent, sPath )
	{
		if( ! rpc_is_save_file )
			return false;
		
		if( sPath == '' )
			sPath = rpc_local_temp_dir;
			
		try 
		{
			var fso, s;
			fso = new ActiveXObject("Scripting.FileSystemObject");
			s = fso.CreateTextFile(sPath+sFileName, true);
			s.writeline(sContent);
			s.Close();
		} 
		catch(err)
		{
			//alert( 'Грешка при запис на файл.\nНепълна подръжна на фунционалностите на ЕОЛ !\nМоля свържете се със системния администратор !\n' );

			return false;
		}

		return true;
	}
	
	function getById(id) {
		obj=document.getElementById(id);
		
		if(!obj)
			return false;
		
		return obj;
	}

	Array.prototype.in_array = function ( obj ) {
		var len = this.length;
		for ( var x = 0 ; x <= len ; x++ ) {
			if ( this[x] == obj ) return true;
		}
		return false;
	}
