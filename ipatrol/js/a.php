
//        try{
//            ajaxRequest.open("GET", "./ajax_scripts/check_connection.php", true);
//            ajaxRequest.onreadystatechange = function(){
//                if( ajaxRequest.readyState == 4 ){
//                    // Следим връзката към сървъра
//                    if( ajaxRequest.status != 200 ){
//                        if(typeof IntelliSOD != "undefined"){
//                            IntelliSOD.playSound('connection',true);
//                            timeout = 60000;
//
//                            //       alert("typeof" + timeout);
//                            //                    var a = new Audio('http://localhost/w8_int/alarm.mp3');
//                            //                    a.play()
//                            //                    a.stop()
//                        }
//                        return;
//                    }
////                else {
////                    timeout = 31000;
////                    IntelliSOD.stopSound();
////                }
//
//                } else if ( ajaxRequest.readyState == 2 && ajaxRequest.status == 200 ) {
//                    timeout = 5000;
//                    if(typeof IntelliSOD != "undefined") {
//                        IntelliSOD.stopSound();
//                    }
//
//                }
//                alert(ajaxRequest.readyState + " / " + ajaxRequest.status);
//            }
//            ajaxRequest.send(null);
//   //         alert("try" + timeout);
//        }catch(e){ // Проблем с отговора
//            alert("catch" + timeout);
//            if(typeof IntelliSOD != "undefined"){
//                IntelliSOD.playSound('connection',true);
//            }
//        }
//

//}
//    alert("window" + timeout);