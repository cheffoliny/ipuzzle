/*
 xmlrpc v2.1.0.  (c) Copyright 2005 by Plamen Dimitrov
 */

///////////////////////////////////////////////////////////////////////////////////////////
// ------------------ Глобални параметри ------------------
// Версията на Браузера
var isIE = false;

// API функцията, която ще посрешне Задачата
var rpc_api_general = "api/api_general.php";

// Форма от която ще се вземат параметрите и ще се предадат към API функцията
var rpc_form = "form1";

// Да се извежда ли debug информация
var rpc_debug = false;

// Разшява дебуга генерално. Наследява се от config.inc.php - define('EOL_DEBUG', TRUE);
var rpc_eol_debug = true;

// Скрипта, който ще се извика от rpc_api_general за да изпълни зявката
var rpc_action_script = "";

// име на <DIV> където щте се визуализира резултата
var rpc_result_area = "result";

// префикс на за ID-тата на елементите в XSL-а
var rpc_prefix = "";

// Дали да се ресайзва резултата в DIV-а или не 
var rpc_resize = "on";

// Дали да се показва панела за експорт към EXCEL
var rpc_excel_panel = "on";

// Дали да се показва пейджинга или не
var rpc_paging = "on";

// Дали да е динамичен резултата да може да се добавят нови редове, редкатират ....
var rpc_edit_report = "off";

// Дали да се показва поле за дата в панела с операции
var rpc_invoice_toolbar = "off";

// Дали да се показва панела с инструменти на справка "Документи за продажба"
var rpc_admin_invoice_toolbar = "off";

// Дали да се показва панела с инструменти на формата за документи за продажба
var rpc_invoice_services_toolbar = "off";

// Дали да се визуализира номерация в резултата
var rpc_autonumber = "on";

// име на XSL файла за резултата
var rpc_xsl = "xsl/general_result.xsl";

// Метод за обръщение към API функция
var rpc_method = "POST";

// Автоматично обиране на параметрите
var rpc_auto_params = true;

// Ако е изключено автоматичното обиране на параметрите то се използва тази променлива
var rpc_manual_params = '';

// функция, която се изпълнява при завършване на работата на RPC-to
var rpc_on_exit = function(){};

// Времена директория на потребителя( използва се от SaveFile( framework_general.js ) )
var rpc_local_temp_dir = "c:\\temp\\";

// Да се прави ли опит за запис на файл ( използва се от SaveFile( framework_general.js ) )
var rpc_is_save_file = "c:\\temp\\";

// Спира изкарването XSL в дебъг прозореца
var rpc_xls_debug = false;

// Спира изкарването HTML в дебъг прозореца
var rpc_html_debug = false;

// Показва подадените параметри форматирано
var rpc_smart_params = false;

var rpc_default_api_action = "result";

var rpc_offset = 0;

window.top['rpc_version'] = 1;


///////////////////////////////////////////////////////////////////////////////////////////
// ------------------ Вътрешни за RPC-то параметри ------------------

// дали в момента се изпълнява Задача към сървъра;
var _rpc_blocked=false;

// Какво да се направи след като прключи успешно работата си RPC-то и не възникнала грешка _rpc_error_value == 0
var _rpc_close_action = 0;

// обекта <DIV> който показва, че страницата се зарежда;
var _rpc_obj_loader;

// код на върната ор API функцията грешка
var _rpc_error_value;

// масив във който се запомня състоянието на инпут елементите във формата
var _rpc_form_elements = new Array ();

// обект - <DIV> за резултата
var _rpc_result;

// Да се извейда ли резултат. Установява се във ForProccess, ако няма резултат това поле си остава 'false' и не се извежда резултат
var _rpc_play_result;

// елемент който да се селектне след изпълнението на RPC-to
var _rpc_selcted_element = false;

// елемент който да се фокусира след изпълнението на RPC-to
var _rpc_focused_element = false;

// обект - прозореца за debug
var _debug_win;

var _debug_win_style="<style>\n body {background-color: #FAFBFE; padding: 0px;border: 0px;margin: 0px;font-family: arial, sans-serif;font-size: 9pt;} font.header{font-weight: bold;} p{font-size: 9pt;}</style>\n";

//if (document.all) { isIE = true; }
if (bowser.msie) { isIE = true; }


///////////////////////////////////////////////////////////////////////////////////////////

// Създава XMLHTTP обект за комуникация със API функция
function getXMLHTTP() {
    var xmlhttp=false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
     try {
     xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
     } catch (e) {
     xmlhttp = false;
     }
     @end @*/
    if(bowser.msie) {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
//			xmlhttp.overrideMimeType('text/xml');
    }
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        isIE=false;
        xmlhttp = new XMLHttpRequest();
        xmlhttp.overrideMimeType('application/xml');
    }
    return xmlhttp;
}


function form2POST()
{
    return getFramework().serialize( $( rpc_form ) );
}

// подменя с стринг - s всички '<', '>' с '&lt;','&gt;' за да се покажат в debug прозореца
function htmlspecialchars(s){
    return "<pre>"+s.replace(/</g,'&lt;').replace(/>/g,'&gt;')+"</pre>";
}

// създава debug прозорец
function create_debug_window(){
    var sizeX=780;
    var sizeY=580;
    if (document.all) var xMax = screen.width, yMax = screen.height;
    else
    if (document.layers) var xMax = window.outerWidth, yMax = window.outerHeight;
    else var xMax = 800, yMax=600;
    var xOffset = (xMax - sizeX)/2, yOffset = (yMax - sizeY)/2;
    if (yOffset > 30) {yOffset = yOffset - 30}
    else yOffset = 0;
    resolveit = 'width=' + sizeX + ', height=' + sizeY + ', directories=0, hotkeys=0, location=0, menubar=0, resizable=1, screenX='+ xOffset +', screenY=' + yOffset +', scrollbars=1, status=0, toolbar=0, left=' + xOffset +', top=' + yOffset;

    _debug_win = window.open('', 'xmlRPC_Debug', resolveit);
    //_debug_win.focus();

    _debug_win.document.open("text/html","replace");
    _debug_win.document.write('<html><head><title>xmlRPC2 Debug Window</title>'+_debug_win_style+'</head><body><table><tr><td>');
}


// функцията се извиква когат RPC-то е приключило работата. Скрива се <DIV> за зареждане, извършва се действието: _rpc_close_action
// функцията се извиква когат RPC-то е приключило работата. Скрива се <DIV> за зареждане, извършва се действието: _rpc_close_action
function DisableLoader(){
    _rpc_blocked=false;

    // Скриване на панела за зареждане
    if(_rpc_obj_loader) _rpc_obj_loader.style.display = 'none';

    // Възстановява disable атрибута на всики елементи във формата
    var oFramework = getFramework();

    oFramework.hideDisabler();


    if( _rpc_focused_element || _rpc_selcted_element )
    {
        if( _rpc_focused_element && _rpc_focused_element.focus)
        {
            _rpc_focused_element.focus();
        }
        if( _rpc_selcted_element && _rpc_selcted_element.select )
        {
            _rpc_selcted_element.select();
        }
    }
    else
    {
        var form = document.getElementById(rpc_form) ;
        if(form)
        {
            for(i=0;i<form.elements.length;i++)
                if	(
                    !form.elements[i].disabled && !form.elements[i].readOnly &&
                    (form.elements[i].tagName.toUpperCase() == 'INPUT') && (form.elements[i].type.toUpperCase() == 'TEXT') &&
                    form.elements[i].tabIndex >=0
                )
                {
                    //dido2k (6.12.2006)
                    try {
                        if( form.elements[i].focus )
                            form.elements[i].focus();

                        if( form.elements[i].select )
                            form.elements[i].select();

                        break;
                    }
                    catch( e )
                    {
                    }
                }
        }
    }

    _rpc_selcted_element = false;
    _rpc_focused_element = false;


    // Изпълнява функцията за приключване на работа : on_rpc_exit
    rpc_on_exit( parseInt( _rpc_error_value ) );

    // shitnq
    jQuery("input[type=text]").keypress(function(event){
        if(parseInt(event.keyCode, 10) == 13){
            jQuery('#form1').find('button[type=submit]').find('img[src="images/confirm.gif"]').parent().last().click();
            return false;
        }
    });

    // Действие след приклучване на работата на скрипта
    if (!_rpc_error_value)
    {
        switch (_rpc_close_action ) {
            case 1:
                if(window.loadXMLDoc) window.loadXMLDoc( rpc_default_api_action );
                break;
            case 2:
                window.close();
                break;
            case 3:
                if(window.opener && !window.opener.closed)
                    if(window.opener.loadXMLDoc)
                        window.opener.loadXMLDoc( rpc_default_api_action );
                window.close();
                break;
            case 4:
                if(window.opener && !window.opener.closed)
                    if(window.opener.loadXMLDoc)
                        window.opener.loadXMLDoc( rpc_default_api_action );

                window.focus();
                break;
            case 5:
                if(window.opener && !window.opener.closed)
                    if(window.opener.loadXMLDoc)
                        window.opener.loadXMLDoc('load');
                window.close();
                break;
            case 6:
                if(window.loadXMLDoc) window.loadXMLDoc('load');
                break;
        }
    }
}



// Обхожда HTML резултата след трансформацията между XML i XSL и изпълнява JS в нея
function  ecxecuteXML(div){

    var aScripts = div.getElementsByTagName('script');
    var aChildScripts = new Array();

    for(var i=0;i<aScripts.length;i++)
    {
        aChildScripts[i] = document.createElement('script');
        aChildScripts[i].text = aScripts[i].text;
    }
    //Pavel
    for(var i=0;i<aChildScripts.length;i++){
        div.appendChild( aChildScripts[i] );
    }
}

// Извиква директно API 
function loadDirect( api_action ) {
    var form = document.getElementById(rpc_form);

    if ( form )
    {
        form.method="POST";
        if( form.api_action )
        {
            form.api_action.value = api_action;
        }
        form.action="api/api_general.php?action_script=" + rpc_action_script + "&api_action=" + api_action + '&rpc_version=' + window.top['rpc_version'];
        form.target = api_action == 'export_to_pdf' ? "_blank" :"";
        form.submit();
    }
}

function loadXMLDoc2( api_action, close_action )
{
    window.top['rpc_version'] = 2;

    loadXMLDoc(api_action, close_action);

    return false;
}


// Задача към API функцията
//		action - дейтвие, което да извърши API функцията ('result', 'update', 'delete', ...) 
//		close_action - дейтвие, което да извърши PRC след приключване на работа и не възникнала грешка _rpc_error_value == 0:
//			0 - няма действие;
//			1 - рефрешва прозореца - window.loadXMLDoc('result')
//			2 - затваря формата - window.close();
//			3 - затваря формата и рефрешва родителския прозорец - window.close; window.opener.loadXMLDoc('result')
function loadXMLDoc( api_action, close_action ) {
    if( !_rpc_blocked ){
        // Ако преди това не е пусната RPC сесия
        var api_action = api_action != undefined ? api_action : 'result';
        _rpc_error_value=0;
        _rpc_blocked=true;
        _rpc_result=document.getElementById(rpc_result_area);

        _rpc_close_action = close_action != undefined ? close_action : 0;

        //Показва панела за зареждане
        _rpc_obj_loader = document.getElementById('loading');
        var faIco = document.createElement('i');

        if( !_rpc_obj_loader ){
            _rpc_obj_loader = document.createElement("DIV");
            _rpc_obj_loader.appendChild(faIco);
            _rpc_obj_loader.id = 'loading';
            _rpc_obj_loader.style.border = '0px';
            _rpc_obj_loader.style.textAlign = 'center';
            _rpc_obj_loader.style.color = 'White';
            _rpc_obj_loader.style.position = 'absolute';
            _rpc_obj_loader.style.right = '50%';
            _rpc_obj_loader.style.bottom = '50%';
            _rpc_obj_loader.style.zIndex = 1000;
            faIco.className = 'fas fa-puzzle-piece fa-pulse text-primary fa-5x';
            document.body.appendChild(_rpc_obj_loader);
        }

        if( _rpc_obj_loader ) _rpc_obj_loader.style.display = 'block';

        if( rpc_debug && rpc_eol_debug )
        {
            if( !_debug_win || _debug_win.closed )
                create_debug_window();
        }

        try {
            var xmlhttp;
            if ( ! xmlhttp )
                xmlhttp = getXMLHTTP();

            getFramework().showDisabler();

            var params = "";
            if(rpc_auto_params)
                params = form2POST();
            else
                params = rpc_manual_params

            // Зареждане на данните
            params += (params == '' ? "" : "&") + 'action_script=' + rpc_action_script + '&api_action=' + api_action + '&rpc_prefix=' + rpc_prefix + '&rpc_result_area=' + rpc_result_area + '&rpc_version=' + window.top['rpc_version'];
            if( rpc_debug && rpc_eol_debug ){
                _debug_win.document.write("<p><font class='header'>loadXMLDoc ( '"+api_action+"', "+_rpc_close_action+" )</font></p><hr>\n");

                if( !rpc_smart_params )
                    _debug_win.document.write("<p><font class='header'>Request to:</font></p><p>"+decodeURIComponent(rpc_api_general+'?'+params)+"</p><hr>\n");
                else
                    _debug_win.document.write("<p><font class='header'>Request to:</font></p><p>"+decodeURIComponent(rpc_api_general+'?'+params).split('&').join('<br />')+"</p><hr>\n");
            }

            if (xmlhttp) {

                if (rpc_method=='GET')
                {
                    xmlhttp.open(rpc_method,rpc_api_general+'?'+params, true);
                } else
                {
                    xmlhttp.open(rpc_method,rpc_api_general, true);
                }

                xmlhttp.onreadystatechange=

                    //  *************** Извличане на XML данни ***************
                    function GetXML(aEvt){

                        if( xmlhttp.readyState == 4 ){

                            if(  rpc_debug && rpc_eol_debug  )
                                _debug_win.document.write("<p><font class='header'>XML Response:</font></p><p>"+htmlspecialchars(xmlhttp.responseText)+'</p><hr>\n');

                            var bHTML = false;

                            try {
                                if( xmlhttp.getResponseHeader('content-type').toLowerCase().indexOf('text/html') != -1 )
                                    bHTML = true;
                            }
                            catch( e ) {}

                            if( bHTML )
                            {
                                var oTarget = $( rpc_result_area );

                                if( oTarget && oTarget.innerHTML )
                                    oTarget.innerHTML = xmlhttp.responseText;

                                DisableLoader();
                                return;
                            }

                            if ( isIE && xmlhttp.responseXML.parseError.errorCode !=0 ){
                                // Проблем в структурата на XML файла
                                if( rpc_debug && rpc_eol_debug ) _debug_win.document.write("<p><font class='header'>XML error :  </font>"+xmlhttp.responseXML.parseError.reason+"</p><hr>");
                                // Скриване на панела за зареждане
                                DisableLoader();

                            } else {
                                // XML файла е ОК

                                var xml=xmlhttp.responseXML;
                                // обработка на XML файла
                                FormProcessing(xml);
                                //alert(_rpc_play_result);
                                // запитване за XSL
                                if ( rpc_xsl && (_rpc_result != null) && (!_rpc_error_value) && (_rpc_play_result) ){
                                    var xslhttp = getXMLHTTP();
                                    if(xslhttp){
                                        xslhttp.open('GET', rpc_xsl, true);
                                        xslhttp.onreadystatechange=

                                            // *************** Извличане на XSL данни ***************
                                            function GetXSL(aEvt){
                                                if(xslhttp.readyState == 4) {
                                                    if (isIE && xslhttp.responseXML.parseError.errorCode !=0){
                                                        // Проблем в структурата на XSL файла
                                                        if( rpc_debug && rpc_eol_debug ) _debug_win.document.write("<p><font class='header'>XSL error :  </font>"+xslhttp.responseXML.parseError.reason+"</p><hr>");
                                                    } else {
                                                        // XSL файла е ОК
                                                        var xsl=xslhttp.responseXML;

                                                        var tag1 = xsl.getElementsByTagName('xsl:param'	);
                                                        var tag2 = xsl.getElementsByTagName('param'		);

                                                        if( tag1.length > tag2.length )
                                                            params = tag1;
                                                        else
                                                            params = tag2;

                                                        if( params ){
                                                            //alert(params.length);
                                                            for (var i=0; i<params.length; i++){
                                                                if( params.item(i).firstChild )
                                                                {
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_result_area' )
                                                                    {
                                                                        params.item(i).firstChild.nodeValue = rpc_result_area;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_prefix' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_prefix") ?  _rpc_result.getAttribute("rpc_prefix") : rpc_prefix;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_resize' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_resize") ?  _rpc_result.getAttribute("rpc_resize") : rpc_resize;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_action_script' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_action_script") ?  _rpc_result.getAttribute("rpc_action_script") : rpc_action_script;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_excel_panel' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_excel_panel") ?  _rpc_result.getAttribute("rpc_excel_panel") : rpc_excel_panel;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_paging' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_paging") ?  _rpc_result.getAttribute("rpc_paging") : rpc_paging;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_edit_report' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_edit_report") ?  _rpc_result.getAttribute("rpc_edit_report") : rpc_edit_report;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_invoice_toolbar' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_invoice_toolbar") ?  _rpc_result.getAttribute("rpc_invoice_toolbar") : rpc_invoice_toolbar;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_admin_invoice_toolbar' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_admin_invoice_toolbar") ?  _rpc_result.getAttribute("rpc_admin_invoice_toolbar") : rpc_admin_invoice_toolbar;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_invoice_services_toolbar' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_invoice_services_toolbar") ?  _rpc_result.getAttribute("rpc_invoice_services_toolbar") : rpc_invoice_services_toolbar;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                    if ( params.item(i).getAttribute('name') == 'rpc_transfer_objects' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_transfer_objects") ?  'on' : 'off';
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }

                                                                    if ( params.item(i).getAttribute('name') == 'rpc_autonumber' )
                                                                    {
                                                                        param = _rpc_result.getAttribute("rpc_autonumber") ?  _rpc_result.getAttribute("rpc_autonumber") : rpc_autonumber;
                                                                        params.item(i).firstChild.nodeValue = param;
                                                                    }
                                                                }
                                                            }
                                                        }


                                                        if( rpc_debug && rpc_eol_debug && rpc_xls_debug )
                                                            _debug_win.document.write("<p><font class='header'>XSL Response:</font></p><p>"+htmlspecialchars(xslhttp.responseText)+'</p><hr>\n');

                                                        if (isIE) {
                                                            // За Internet Explore

                                                            try {
                                                                //var innerHTML_string = 	xml.transformNode(xsl);
                                                                var innerHTML_string = magicXML.transform(xmlhttp.responseText,xslhttp.responseText);
                                                            }
                                                            catch( e )
                                                            {
                                                                alert( e.message );
                                                            }

                                                            _rpc_result.innerHTML = '';
                                                            _rpc_result.innerHTML = innerHTML_string;

                                                            // eval-ва JS в резултата
                                                            ecxecuteXML(_rpc_result);

                                                            if( rpc_debug && rpc_eol_debug && rpc_html_debug ) _debug_win.document.write("<p><font class='header'>HTML :  </font></p>"+htmlspecialchars(_rpc_result.innerHTML)+"</p><hr>");

                                                        } else {
                                                            // За Мозила
                                                            var xsltProcessor = new XSLTProcessor();
                                                            xsltProcessor.importStylesheet(xsl);
                                                            var fragment = xsltProcessor.transformToFragment(xml, document);

                                                            _rpc_result.innerHTML = "";
                                                            _rpc_result.appendChild(fragment);

                                                            // eval-ва JS в резултата
                                                            ecxecuteXML(_rpc_result);

                                                            if( rpc_debug && rpc_eol_debug ) _debug_win.document.write("<p><font class='header'>HTML :  </font></p>"+htmlspecialchars(_rpc_result.innerHTML)+"</p><hr>");
                                                        }
                                                        FormProcessing_action(xml);
                                                    }
                                                    // Скриване на панела за зареждане
                                                    DisableLoader();
                                                }
                                            }
                                        // ******************************

                                        xslhttp.send(null);
                                    } else DisableLoader();
                                } else {
                                    FormProcessing_action(xml);
                                    DisableLoader();
                                }
                            }
                        }
                    }
                //  ******************************
                xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                if (rpc_method=='GET')
                {
                    xmlhttp.send(null);
                } else {
                    xmlhttp.send(params);
                }
            } else DisableLoader();
        }
        catch(e) {
            // Грешка при създаването на връзка към API
            var msg = (typeof e == "string") ? e : ((e.message) ? e.message : "Unknown Error");
            if( rpc_debug && rpc_eol_debug )
                _debug_win.document.write("<hr><p><font class='header'>RPC error:</font></p><p>"+msg+'</p><hr>\n');
            DisableLoader();
        }

        if( rpc_debug && rpc_eol_debug )
            _debug_win.document.write("</td></tr></table></body>");
    }
    return false;
}

function FormProcessing(xml){
    var response_code=0;
    var response_message='';
    _rpc_play_result=false;

    if( !xml || !xml.documentElement ) return;
    response = xml.documentElement;

    responsedNodes = response.childNodes;

    if( rpc_debug && rpc_eol_debug )
        _debug_win.document.write("<p><font class='header'>XML process</font></p>\n");

    for (var i=0; i<responsedNodes.length; i++) {
        var node = responsedNodes.item(i);

        switch (node.nodeName){
            case 'error' :
                response_code = node.getElementsByTagName("code").length>0 ? node.getElementsByTagName("code").item(0).firstChild.nodeValue : 'undefined';
                response_message = node.getElementsByTagName("message").length>0 ? node.getElementsByTagName("message").item(0).firstChild.nodeValue : 'undefined';
                _rpc_error_value = response_code;

                if (response_code>0)
                    alert('Грешка ('+response_code+') : \n\n'+response_message+'\n\n');
                if( rpc_debug && rpc_eol_debug )
                    _debug_win.document.write("<p><font class='header'>error code: </font> "+response_code+" <font class='header'>error message: </font> "+response_message+'</p>\n');
                break;

            case 'result' :
                _rpc_play_result = true;
                break;

            case 'tree_data' :
                _rpc_play_result = true;
                break;

            case 'debug' :
                for (var deb=0; deb<node.childNodes.length; deb++)
                    if (node.childNodes.item(deb).nodeType==1){
                        element_node = node.childNodes.item(deb);

                        debug_info = element_node.getElementsByTagName("info").length>0 ? element_node.getElementsByTagName("info").item(0).firstChild.nodeValue : 'undefined';
                        debug_file = element_node.getElementsByTagName("file").length>0 ? element_node.getElementsByTagName("file").item(0).firstChild.nodeValue : 'undefined';
                        debug_line = element_node.getElementsByTagName("line").length>0 ? element_node.getElementsByTagName("line").item(0).firstChild.nodeValue : 'undefined';

                        if( rpc_debug && rpc_eol_debug )
                            _debug_win.document.write("<p><font class='header'>Debug info: </font><pre> "+debug_info+" </pre><font class='header'><br/>file: </font><a href='file:/"+debug_file+"'>"+debug_file+"</a> <font class='header'><br/>line: </font> "+debug_line+'</p>\n');
                    }
                break;
        }
    }
    if( rpc_debug && rpc_eol_debug )
        _debug_win.document.write("<hr>\n");
}

function FormProcessing_action(xml){
    if( !xml || !xml.documentElement ) return;
    response = xml.documentElement;
    responsedNodes = response.childNodes;

    if( rpc_debug && rpc_eol_debug )
        _debug_win.document.write("<p><font class='header'>ACTION</font></p>\n");

    for (var i=0; i<responsedNodes.length; i++) {
        var node = responsedNodes.item(i);

        switch (node.nodeName){
            case 'action':
                for (var a=0; a<node.childNodes.length; a++) {
                    action_node = node.childNodes.item(a);
                    switch (action_node.nodeName){
                        case 'alert' :
                            if(action_node.firstChild && (action_node.firstChild.nodeValue != null))
                                alert(action_node.firstChild.nodeValue);
                            break;

                        case 'form' :
                            var form_id = action_node.getAttribute("id");
                            // if (document.getElementById(form_id))
                            // Формата съществува
                            for (var f=0; f<action_node.childNodes.length; f++)
                                if (action_node.childNodes.item(f).nodeType==1){

                                    element_node = action_node.childNodes.item(f);
                                    element_id = element_node.getAttribute("id");

                                    if (document.getElementById(element_id)){
                                        // Елемента съществува
                                        el=document.getElementById(element_id);
                                        // Установяване елементите на списък
                                        if(el.tagName=='SELECT'){
                                            if(element_node.childNodes.length>0){
                                                notempty=true;
                                                for (var s=0; s<element_node.childNodes.length; s++)
                                                    if (element_node.childNodes.item(s).nodeType==1){
                                                        if(notempty){
                                                            el.innerHTML = "";
                                                            notempty=false;
                                                        }
                                                        option_node = element_node.childNodes.item(s);
                                                        nOP = document.createElement("OPTION");
                                                        el_value=option_node.firstChild != null ? option_node.firstChild.nodeValue : "";
                                                        nOP.appendChild(document.createTextNode(el_value));
                                                        nOP.id = option_node.getAttribute("id") != null ? option_node.getAttribute("id") : 0;
                                                        nOP.value = option_node.getAttribute("value") != null ? option_node.getAttribute("value") : 0;
                                                        if(!empty(option_node.getAttribute("padding-left"))) {
                                                            nOP.style.paddingLeft = option_node.getAttribute("padding-left");
                                                        }

                                                        nOP.selected = option_node.getAttribute("selected") != null || element_node.getAttribute("value") == nOP.value;
                                                        el.appendChild(nOP);
                                                        if( rpc_debug && rpc_eol_debug )
                                                            _debug_win.document.write("<p><font class='header'>select : "+element_id+"</font> option -> "+el_value+'</p>\n');
                                                    }
                                            }
                                        }

                                        // Установяване стойностите на елементите
                                        if( element_node.firstChild && ( element_node.firstChild.nodeValue != null ) && element_node.firstChild.nodeType != 1 && element_node.firstChild.nodeValue.trim() != "")
                                        {

                                            switch( el.tagName.toLowerCase() )
                                            {
                                                case 'button':
                                                    setButtonValue(el, element_node.firstChild.nodeValue);
                                                    break;
                                                default:
                                                {

                                                    try {
                                                        if( typeof( el.value ) != "undefined" )
                                                        {

                                                            el.value = element_node.firstChild.nodeValue;
                                                        }
                                                        else
                                                        {
                                                            if( el.firstChild )
                                                                el.firstChild.nodeValue = element_node.firstChild.nodeValue;
                                                            else
                                                                el.appendChild( document.createTextNode( element_node.firstChild.nodeValue ) );
                                                        }
                                                    }
                                                    catch( e )
                                                    {
                                                    }
                                                }
                                            }

                                            if( el.lenght > 0 && el.tagName.toLowerCase() == 'select' && typeof( el.onchange ) != "undefined" && el.onchange != null )
                                                el.onchange();

                                            if( rpc_debug && rpc_eol_debug )
                                                _debug_win.document.write("<p><font class='header'>id : "+element_id+"</font> value -> "+element_node.firstChild.nodeValue+'</p>\n');
                                        }

                                        // Установяване атрибутите на елементите
                                        for(attr=0; attr<element_node.attributes.length; attr++)
                                        {
                                            if(element_node.attributes[attr].name == 'id')
                                                continue;

                                            switch( element_node.attributes[attr].name.toLowerCase() ) {
                                                case 'value' :
                                                {
                                                    if( el.tagName.toLowerCase() == 'button' )
                                                    {
                                                        setButtonValue(el, element_node.attributes[attr].value);
                                                    }
                                                    else
                                                    {
                                                        try {
                                                            if( typeof( el.value ) != "undefined" )
                                                            {
                                                                el.value = element_node.attributes[attr].value;
                                                            }
                                                            else
                                                            {
                                                                if( el.firstChild )
                                                                    el.firstChild.nodeValue = element_node.attributes[attr].value;
                                                                else
                                                                    el.appendChild( document.createTextNode( element_node.attributes[attr].value ) );
                                                            }
                                                        }
                                                        catch( e )
                                                        {
                                                        }
                                                    }

                                                    if( el.tagName.toLowerCase() == 'select' && typeof( el.onchange ) != "undefined" && el.onchange != null )
                                                        el.onchange();
                                                }
                                                    break;

                                                case 'selected' :
                                                    _rpc_selcted_element = el;
                                                    break;

                                                case 'focused' :
                                                    _rpc_focused_element = el;
                                                    break;

                                                case 'visibility' :
                                                    el.style.visibility = element_node.attributes[attr].value;
                                                    break;

                                                case 'display' :
                                                    el.style.display = element_node.attributes[attr].value;
                                                    break;
                                                case 'align' :
                                                    el.style.textAlign = element_node.attributes[attr].value;
                                                    break;
                                                case 'valign' :
                                                    el.style.verticalAlign = element_node.attributes[attr].value;
                                                    break;
                                                case 'background-color':
                                                    el.style.backgroundColor = element_node.attributes[attr].value;
                                                    break;
                                                case 'color':
                                                    el.style.color = element_node.attributes[attr].value;
                                                    break;
                                                case 'text-decoration':
                                                    el.style.textDecoration = element_node.attributes[attr].value;
                                                    break;
                                                case 'font-weight':
                                                    el.style.fontWeight = element_node.attributes[attr].value;
                                                    break;
                                                case 'readonly':
                                                    el.readOnly = element_node.attributes[attr].value;
                                                    break;
                                                case 'disabled':
                                                    el.disabled = element_node.attributes[attr].value;
                                                    break;
                                                default :
                                                    el.setAttribute(element_node.attributes[attr].name, element_node.attributes[attr].value);

                                            }

                                            if( rpc_debug && rpc_eol_debug )
                                                _debug_win.document.write("<p><font class='header'>id : "+element_id+"</font> "+element_node.attributes[attr].name+"->"+element_node.attributes[attr].value+'</p>\n');
                                        }

                                    }else{

                                        if( rpc_debug && rpc_eol_debug )
                                            _debug_win.document.write("<p><font class='header'>id : "+element_id+"</font> <a  style='color:red;'>Елементът не е открит</a></p>");
                                    }
                                }
                            break;
                    }
                }
                break;
        }
    }
    if( rpc_debug && rpc_eol_debug )
        _debug_win.document.write("<hr>\n");
}

/*
 Функцията се налага поради несъвместимост на IE и Firefox по отношение на атрибута 'value' на елемента <button>

 @name setButtonValue
 @param oEl object елемента <button>
 @param string стойноста на бутона, която ще се постави да елемента
 @return void
 @author dido2k
 */

function setButtonValue(oEl, sValue)
{
    if( oEl )
    {
        if( oEl.childNodes[0] )
            oEl.childNodes[0].nodeValue = sValue;
        else if( typeof( oEl.innerHTML ) != 'undefined' )
            oEl.innerHTML = sValue;
        else if( typeof( oEl.value ) != 'undefined' )
            oEl.value = sValue;
    }
}
