function onFormKeyNum( event )
{
    var keyCode = document.all? event.keyCode : event.which;
    var dRes = ( keyCode >= 0x30 && keyCode <= 0x39 );

    event.returnValue = dRes;
    return dRes;
}

function getHTTPObject(){
    var ajaxRequest;  // The variable that makes Ajax possible!

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
        return ajaxRequest;
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
            return ajaxRequest;
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                return ajaxRequest;
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return null;
            }
        }
    }
}

function no_test() {
    var timeout = 15000;

    httpObject = getHTTPObject();
    if( httpObject != null || (typeof httpObject != "undefined") ) {

        httpObject.timeout = timeout;

        httpObject.onreadystatechange = function () {
            if (httpObject.readyState == 4) {

                if (httpObject.status != 200) {

                } else {

                }

                var ajaxDisplay = document.getElementById('no_test');
                ajaxDisplay.innerHTML = httpObject.responseText;

            }
        }

        if (document) {
            var sig = document.getElementById('test_type').value;
            httpObject.open("GET", "./ajax/ajax_no_test.php?sig=" + sig, true);
            httpObject.send(null);
            window.setTimeout("no_test(" + sig + ")", timeout);
        }

    }
}


function no_220() {
    var timeout = 300000;

    httpObject = getHTTPObject();

    if( httpObject != null || (typeof httpObject != "undefined") ) {

        httpObject.timeout = timeout;

        httpObject.onreadystatechange = function () {
            if (httpObject.readyState == 4) {

                if (httpObject.status != 200) {

                } else {

                }

                var ajaxDisplay = document.getElementById('no_220');
                ajaxDisplay.innerHTML = httpObject.responseText;

            }
        }

        if (document) {
            var sig = document.getElementById('ac_dc').value;
            httpObject.open("GET", "./ajax/ajax_no_220.php?sig=" + sig, true);
            httpObject.send(null);
            window.setTimeout("no_220(" + sig + ")", timeout);
        }
    }
}


function low_level() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 300000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('low_level');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_low_level.php", true);
        ajaxRequest.send(null);
        window.setTimeout("low_level()", timeout);
    }

}

function stuck_in_objects() {
    var timeout = 300000;
    var httpObject = getHTTPObject();

    if( httpObject != null || (typeof httpObject != "undefined") ) {

        httpObject.timeout = timeout;

        httpObject.onreadystatechange = function () {
            if (httpObject.readyState == 4) {

                if (httpObject.status != 200) {

                } else {

                }

                var ajaxDisplay = document.getElementById('stuck_in_objects');
                ajaxDisplay.innerHTML = httpObject.responseText;

            }
        }
    }

    if( document ) {
        httpObject.open("GET", "./ajax/ajax_stuck_in_objects.php", true);
        httpObject.send(null);
        window.setTimeout("stuck_in_objects()", timeout);
    }

}

function in_service_mod() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 300000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('in_service_mod');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_in_service_mod.php", true);
        ajaxRequest.send(null);
        window.setTimeout("in_service_mod()", timeout);
    }
}

function in_temp_bypass() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 300000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('in_temp_bypass');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_in_temp_bypass.php", true);
        ajaxRequest.send(null);
        window.setTimeout("in_temp_bypass()", timeout);
    }
}


function no_closed_objects() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 300000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('no_closed_objects');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_no_closed_objects.php", true);
        ajaxRequest.send(null);
        window.setTimeout("no_closed_objects()", timeout);
    }
}


function stop_alarms( sID ) {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 300000;
    var sID     = sID;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('stop_alarms');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_stop_alarms.php?sID=" + sID, true);
        ajaxRequest.send(null);
        window.setTimeout("stop_alarms(0)", timeout);
    }
}



function monitoring() {

    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000;
    var play = 0;
    var receiver = 0;
    var test11 = 0;
    var test14 = 0;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){
            } else {
            }

            var ajaxDisplay = document.getElementById('monitoring');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document )
    {

        if( document.getElementById('num').value ) {
            var num = document.getElementById('num').value;
        } else {
            var num = 0;
        }

        if( $('#test_id_11').prop('checked') ) {
            var test11 = 1;
        }
        if( $('#test_id_14').prop('checked') ) {
            var test14 = 1;
        }
        //var test14 = document.querySelector('input[name="test_id_14"]:checked').value;
        var play = document.getElementById('type_signal').value;
        var receiver = document.getElementById('id_receiver').value;
        var queryString = "num=" + num + "&play=" + play + "&receiver=" + receiver + "&test11=" + test11 + "&test14=" + test14;

        ajaxRequest.open("GET", "./ajax/ajax_monitoring.php?" + queryString, true);
        ajaxRequest.send(null);
        window.setTimeout("monitoring()", timeout);

    }
}

function alarms_by_day() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('alarms_by_day');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_alarms_by_day.php", true);
        ajaxRequest.send(null);
        window.setTimeout("alarms_by_day()", timeout);
    }
}

function alarms_by_type() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('alarms_by_type');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_alarms_by_type.php", true);
        ajaxRequest.send(null);
        window.setTimeout("alarms_by_type()", timeout);
    }
}

function alarms_by_signal() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('alarms_by_signal');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_alarms_by_signal.php", true);
        ajaxRequest.send(null);

        window.setTimeout("alarms_by_signal()", timeout);
    }
}


function alarms_by_object() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('alarms_by_object');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {

        var start_date    = document.getElementById('start_date').value;
        var end_date      = document.getElementById('end_date').value;
        var diff_interval = document.getElementById('diff_interval').value;
        var count_alarms  = document.getElementById('count_alarms').value;
        var type_alarms   = document.getElementById('type_alarms').value;
        var office        = document.getElementById('office').value;

        ajaxRequest.open("GET", "./ajax/ajax_alarms_by_object.php?start_date=" + start_date + "&end_date=" + end_date + "&type_alarms=" + type_alarms + "&count_alarms=" + count_alarms + "&diff_interval=" + diff_interval + "&office=" + office , true);
        ajaxRequest.send(null);
        window.setTimeout("alarms_by_object()", timeout);
    }
}

function alarms_by_object_detailed() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('alarms_by_object_detailed');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {

        var start_date    = document.getElementById('start_date_d').value;
        var end_date      = document.getElementById('end_date_d').value;
        var diff_interval = document.getElementById('diff_interval_d').value;
        var count_alarms  = document.getElementById('count_alarms_d').value;
        var type_alarms   = document.getElementById('type_alarms_d').value;
        var office        = document.getElementById('office_d').value;

        ajaxRequest.open("GET", "./ajax/ajax_alarms_by_object_detailed.php?start_date=" + start_date + "&end_date=" + end_date + "&type_alarms=" + type_alarms + "&count_alarms=" + count_alarms + "&diff_interval=" + diff_interval + "&office=" + office , true);
        ajaxRequest.send(null);
        window.setTimeout("alarms_by_object_detailed()", timeout);
    }
}


function refreshDiv( divId ){

    var container = divId;
    var content = container.innerHTML;
    container.innerHTML= content;
}


function set_signal_type( sID ) {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var sID = sID;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){ } else { }
            var ajaxDisplay = document.getElementById('control-sidebar-home-tab');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_get_signals_list.php?sID=" + sID , true);
        ajaxRequest.send(null);

    }
}


function alarms_by_delay() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('alarms_by_delay');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        var time_interval = document.getElementById('time_interval').value;
        var time_delay    = document.getElementById('time_delay').value;
        ajaxRequest.open("GET", "./ajax/ajax_alarms_by_delay.php?time_interval=" + time_interval + "&time_delay=" + time_delay , true);
        ajaxRequest.send(null);
        window.setTimeout("alarms_by_delay()", timeout);
    }

}

var timeoutHandle = window.setTimeout("selectSignals()", 60000);
function is_signal( sID, flag, strType ) {

    var ajaxRequest;
    var vID   = sID;
    var sFlag = flag;
    var strType = strType;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){
            } else {
            }

            if( strType == 0 ) {
                var ajaxDisplay = document.getElementById('is_signal');
            }
            else {
                var ajaxDisplay = document.getElementById('repeat_signal');
            }
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        if( strType == 0 ) {
            ajaxRequest.open("GET", "./ajax/ajax_is_signals.php?vID=" + vID + "&sFlag=" + sFlag, true);
        } else {
            ajaxRequest.open("GET", "./ajax/ajax_repeats_signals.php?vID=" + vID + "&sFlag=" + sFlag, true);
        }
        ajaxRequest.send(null);
        window.clearTimeout(timeoutHandle);
        timeoutHandle = window.setTimeout("selectSignals()", 60000);

    }

}


function selectSignals( stype ) {
    var stringSignals   = 0;
    var flag            = 0;
    var rflag            = 0;
    //var strType         = stype;
    var strType = (typeof stype === 'undefined') ? 0 : stype;


    if( document ) {

        if( strType == 0 || strType === undefined ) {
            var checkedsignals = document.querySelector('#form1').querySelectorAll('input[name="signals"]:checked');

            if ($('#flag').prop('checked')) {
                var flag = 1;
            }

        } else {

            var checkedsignals = document.querySelector('#form2').querySelectorAll('input[name="signals"]:checked');

            if ($('#rflag').prop('checked')) {
                var flag = 1;
            }

        }

        for (var i = 0; i < checkedsignals.length; i++) {
            stringSignals += ',' + checkedsignals[i].value;
        }
        is_signal(stringSignals, flag, strType); return false;

    }
}


function no_client_no_taxes() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('no_client_no_taxes');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_no_client_no_taxes.php", true);
        ajaxRequest.send(null);
        window.setTimeout("no_client_no_taxes()", timeout);
    }
}


function prepaid_taxes() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 3000000;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                alert("Your browser broke!");
                return false;
            }
        }
    }

    if(typeof ajaxRequest != "undefined"){
        ajaxRequest.timeout = timeout;
    }

    ajaxRequest.onreadystatechange = function(){
        if( ajaxRequest.readyState == 4 ){

            if( ajaxRequest.status != 200 ){

            } else {

            }

            var ajaxDisplay = document.getElementById('prepaid_taxes');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;

        }
    }

    if( document ) {
        ajaxRequest.open("GET", "./ajax/ajax_finance_prepraid_taxes.php", true);
        ajaxRequest.send(null);
        window.setTimeout("prepaid_taxes()", timeout);
    }
}

var httpObject = null;