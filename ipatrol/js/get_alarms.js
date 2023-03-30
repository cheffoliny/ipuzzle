function get_alarms()
{
    var timeout = 10000;
    var ajaxRequest;  // The variable that makes Ajax possible!

//    <img src="images/onload.jpg" onload="$(document).ready(function(){ $(\'myModal\').modal(\'hiden\').remove(); });" />
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

    if(typeof ajaxRequest.timeout != 'undefined'){
        ajaxRequest.timeout = timeout;
    }

    // Create a function that will receive data sent from the server
    ajaxRequest.onreadystatechange = function(){
        if(ajaxRequest.readyState == 4){

            // Следим връзката към сървъра
//			if( ajaxRequest.status != 200 ){
//                if(typeof IntelliSOD != "undefined"){
//                    IntelliSOD.playSound('connection',true);
//                    var a = new Audio('http://localhost/w8_int/alarm.mp3');
//                    a.play()
//                    a.stop()
//                }
//                return;
//
//			} else if( document.getElementById(last_selected_alarm_id) == 0 ) {
//                if(typeof IntelliSOD != "undefined"){
//                    IntelliSOD.stopSound();
//                }
//            }

            var ajaxDisplay = document.getElementById('alarmPanel');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
            if( last_selected_alarm_id && document.getElementById(last_selected_alarm_id) ){
                makeBordered(document.getElementById(last_selected_alarm_id));
                // Refresh and remove content window
                document.getElementById('main').innerHTML = loadXMLDoc( 'action.php?action=home&aID=' + last_selected_alarm_id, 'main', 'home' );

            } //else {
            //	document.getElementById('main').innerHTML = loadXMLDoc( 'action.php?action=home&aID=0', 'main', 'home' );
            // End of content window
            //}
        }
    }

    if( (document) ) {
        ajaxRequest.open("GET", "./ajax_scripts/get_alarms.php", true);
        ajaxRequest.send(null);
        window.setTimeout("get_alarms()", timeout);
    }

}

var last_selected_alarm_id;
function makeBordered(elem){
    var alarmPanelChilds = document.getElementById('alarmPanel').childNodes;
    for( var i = 0; i < alarmPanelChilds.length; i++ ){
        if( alarmPanelChilds[i].tagName == "DIV" ){
            alarmPanelChilds[i].style.borderBottom  = "3px solid transparent";
            alarmPanelChilds[i].style.borderTop     = "3px solid transparent";
            alarmPanelChilds[i].style.borderRight   = "3px solid transparent";
        }
    }
    last_selected_alarm_id = elem.id;
    elem.style.borderBottom = "3px solid #2D89EF";
    elem.style.borderTop    = "3px solid #2D89EF";
    elem.style.borderRight  = "3px solid #2D89EF";
}


function play_alarms() {
    var ajaxRequest;  // The variable that makes Ajax possible!
    var timeout = 10000;

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
            var ajaxDisplay = document.getElementById('play_alarms');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
        }
    }

    if( document ) {
        if( document.getElementById('play') ) {
            var play = document.getElementById('play').value;
        } else {
            var play = 0;
        }

        var queryString = "play=" + play;
        ajaxRequest.open("GET", "./ajax_scripts/play_alarms.php?" + queryString, true);
        ajaxRequest.send(null);
        window.setTimeout("play_alarms()", timeout);
    }

}


function connection_check( c ) {

    var eventLoopInterval = 10000;
    var cnt = c;

    $.ajax({
        cache: false,
        url: "./ajax_scripts/check_connection.php",
        method: "POST",
        timeout: 15000
    }).done(function (data) {
        //console.log(data);
        if( document.getElementById('play') ) {
            var play = document.getElementById('play').value;
            //alert('playval');
        } else {
            var play = 0;
        }
        //alert(play + " / " + IntelliSOD);
        if(typeof IntelliSOD != "undefined" && play != 1 ) {
            IntelliSOD.stopSound();
        }
        setTimeout( connection_check(0), eventLoopInterval );
    }).fail(function (jqXHR, textStatus) {

        if( cnt > 2000 && cnt < 2002 ) {
            if( textStatus == "timeout" ) {
                //alert('textstaError');
                //console.log('timeout');
            } else {
                IntelliSOD.stopSound();
            }
            if( typeof IntelliSOD != "undefined" ){
                IntelliSOD.playSound( 'connection', true );
            }

        }

        eventLoopInterval = eventLoopInterval * 10;
        cnt = cnt + 1;
        //alert(eventLoopInterval + ' / ' + cnt);
        setTimeout( connection_check(cnt), eventLoopInterval );
        //console.log(eventLoopInterval);
    });
}