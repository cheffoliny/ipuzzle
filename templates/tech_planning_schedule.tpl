{literal}

    <script>
        rpc_debug = true;
        rpc_xsl = "xsl/tech_planning_schedule.xsl";

        /**
         *	Site-specific configuration settings for Highslide JS
         */
        /* global hs */
      //   hs.graphicsDir = './js/highcharts/graphics/';
      //   hs.outlineType = 'rounded-white';
      //   hs.wrapperClassName = 'draggable-header';
      //   hs.captionEval = 'this.a.title';
      //   hs.showCredits = false;
      //   hs.allowMultipleInstances = false;
      // //  hs.Expander.prototype.onAfterExpand = addChartToExpand;
      //   hs.Expander.prototype.onBeforeClose = deleteChartFromExpand;
      //
      //   jQuery(document).ready(function() {
      //       jQuery(".in_team").css('backgroundColor','red');
      //
      //       jQuery('#result').on( 'click', '.open-team', function () {
      //           var $row = jQuery(this),
      //               numTeam = $row.parents('td[data-numteam]').data('numteam'),
      //               nIDOff = $('nIDOffice');
      //
      //           if( nIDOff.value != 0 ) {
      //               dialogSetSetupTechTeams(nIDOff.value , numTeam);
      //           }
      //           else {
      //               alert("Грешка с определяне на офиса!");
      //           }
      //       })
      //   });

        function onInit() {
            if (parent.document.getElementById('id_request').value > 0) {
                jQuery('#to_tech_request').hide();
            }

            $('pMonth').style.display = "none";
            hideData();
            loadXMLDoc2('load');

            rpc_on_exit = function() {

                jQuery.each(jQuery(".in_team"),function(index, value){
                    var color = jQuery(value).data('color');
                    var team_num = jQuery(value).data('teamnum');
//                    jQuery(value).append('<span class="glyphicon glyphicon-user pull-right" style="color:#'+color+'"></span>');
                    jQuery(value).append('<span class="badge pull-right open-team" style="background-color:#'+color+'">'+team_num+'</span>');

                });

//                jQuery.each(jQuery(".real_graph"),function(index, value){
//                    jQuery(value).append('<i class="glyphicon glyphicon-info-sign"></i>');
//                });
//                jQuery(".in_team").append('<span class="glyphicon glyphicon-user pull-right" color="#'+jQuery(this).parent().data('color')+'"></span>');
                rpc_on_exit = function() {};
            }
        }

        function getResult() {
            if( $('nIDOffice').value != 0 ) {
                $('start').value = '0';
                $('end').value = '0';
                loadXMLDoc2('result');
            }

            rpc_on_exit = function() {
                jQuery.each(jQuery(".in_team"),function(index, value){
                    var color = jQuery(value).data('color');
                    var team_num = jQuery(value).data('teamnum');

//                    jQuery(value).append('<span class="glyphicon glyphicon-user pull-right" style="color:#'+color+'"></span>');
                    jQuery(value).append('<span class="badge pull-right open-team" style="background-color:#'+color+'">'+team_num+'</span>');


                });

//                jQuery.each(jQuery(".real_graph"),function(index, value){
//                    jQuery(value).append('<i class="glyphicon glyphicon-info-sign"></i>');
//                });
//                jQuery(".in_team").append('<span class="glyphicon glyphicon-user pull-right" color="#'+jQuery(this).parent().data('color')+'"></span>');

//                jQuery(".in_team").append('<span class="glyphicon glyphicon-user"></span>');
                rpc_on_exit = function() {};
            }
        }

        function open_tech_teams() {
            if( $('nIDOffice').value != 0 ) {
                dialogSetSetupTechTeams($('nIDOffice').value);
            }
        }

        function nextDate(act) {
            var oldDate = $('date').value;

            var oldDD = parseInt(parseFloat(oldDate.substr(0,2)));
            var oldMM = parseInt(parseFloat(oldDate.substr(3,2)));
            var oldYY = parseInt(oldDate.substr(6,4));

            var newDate = new Date();

            newDate.setFullYear(oldYY,oldMM-1,oldDD);

            if (act == 'next') {
                newDate.setDate(newDate.getDate() + 1);
            } else {
                newDate.setDate(newDate.getDate() - 1);
            }

            var newDD = newDate.getDate();
            var newMM = newDate.getMonth()+1;
            var newYY = newDate.getFullYear();

            if( newDD < 10 ) newDD = "0" + newDD;
            if( newMM < 10 ) newMM = "0" + newMM;

            var sDate = newDD+'.'+newMM+'.'+newYY;
            $('date').value = sDate;
            getResult();
        }

        function nextMonth(act) {

            var oldDate = $('dateM').value;
            var MM = oldDate.substr(0,2);
            var YY = oldDate.substr(3,4);

            if(act == 'next') {
                MM++;
                if(MM == '13') {
                    MM = '1';
                    YY++;
                }
            } else {
                MM--;
                if(MM == '0') {
                    MM = '12';
                    YY--;
                }
            }

            if(MM < 10) MM = "0" + MM;

            $('dateM').value = MM + '.' + YY;
            getResult();
        }

        function changeType(type) {
            if( type == 'day') {
                $('pDay').style.display = "block";
                $('pMonth').style.display = "none";
            } else {
                $('pDay').style.display = "none";
                $('pMonth').style.display = "block";
            }
        }

        function openPerson(id) {
            if($('id_request_from_contract').value == '0') {
                dialogPerson(id);
            }
        }
        function openPersonalCard(id) {
//			if($('id_request_from_contract').value == '0') {
            var data = id+'@'+this.document.getElementById('date').value;
            dialogPersonalCard(data);
//			}
        }

        function openLimitCard( nIDLimitCard ) {
//			if($('id_request_from_contract').value == '0') {
//				if( parseInt( nIDLimitCard ) )
            dialogLimitCard(nIDLimitCard, nIDLimitCard);
//			}
        }

        function planning(person,col,row_num) {
            id_this = person + ',' + col + ',' +row_num;

            if( $('start').value == '0' ) {     // Ако за пръв път маркирваме квадратче

                $(id_this).style.backgroundColor = '#612c2c';
                $('start').value = id_this;
                $('end').value = id_this;
            } else {
                var aStart = new Array();
                var aEnd = new Array();

                aStart = $('start').value.split(',');
                aEnd = $('end').value.split(',');

                if ( aStart[0] == person ) {           // Ако маркирваме квадратче на същия ред на последното маркирано

                    if( aStart[0] == aEnd[0] && aStart[1] != aEnd[1]  ) {      // Ако вече имаме повече от едно маркирано квадратче,
                        for( i = aStart[1] ; i <= aEnd[1] ; i++ ) { 			//изчистваме ги,
                            id = aStart[0] + ',' + i + ',' + row_num;
                            var row_color = row_num % 2 ? '#FFFFFF' : '#F0F0F0';
                            $(id).style.backgroundColor = row_color;
                        }
                        $(id_this).style.backgroundColor = '#612c2c';         // и маркираме само едно квадратче
                        $('start').value = id_this;
                        $('end').value = id_this;

                    } else {                 		 // случай в който имаме едно маркирано квадратче и маркирваме второ на съшия ред
                        var start,end;

                        if ( col < aStart[1] ) {			      // ако второ маркирано се намира преди първото
                            start = col;
                            end = aStart[1];
                            $('start').value = id_this;
                        } else {                				// ако второто маркирано се намира след първото
                            start = aStart[1];
                            end = col;
                            $('end').value = id_this;
                        }

                        var bad = 0;

                        for( i = start ; i <= end ; i++ ) {     //  проверявам дали между двете квадратчета мястото е свободно
                            id = person + ',' + i + ',' + row_num;
                            //if( $(id) != '[object]') bad = 1;
                            if( $(id) == null) bad = 1;

                        }

                        if( bad == 0 ) {			// ако е свободно чертая
                            for( i = start ; i <= end ; i++ ) {
                                id = person + ',' + i + ',' + row_num;
                                $(id).style.backgroundColor = '#612c2c';
                            }
                        } else { 					// ако не е, изтривам старото и маркирам само новото
                            var row_color = row_num % 2 ? '#FFFFFF' : '#F0F0F0';
                            $(aStart).style.backgroundColor = row_color;

                            $('start').value = $('end').value = id_this;
                            $(id_this).style.backgroundColor = '#612c2c';
                        }
                    }
                } else {			// ако маркираме квадратче на друг ред от реда на последната маркировка

                    for( i = aStart[1] ; i <= aEnd[1] ; i++ ) {			// изчистваме последната маркировка
                        id = aStart[0] + ',' + i + ',' + aStart[2];
                        var row_color = aStart[2] % 2 ? '#FFFFFF' : '#F0F0F0';
                        $(id).style.backgroundColor = row_color;
                    }
                    $(id_this).style.backgroundColor = '#612c2c';      // и маркираме квадратчето
                    $('start').value = id_this;
                    $('end').value = id_this;
                }

            }

        }

        function save() {
            $('id_request').value = parent.document.getElementById('id_request').value;

            if ($('id_request_from_contract').value != 0) {
                $('id_request').value = $('id_request_from_contract').value;
            }

            loadXMLDoc2('planning', 1);
            rpc_on_exit = function() {
                parent.document.getElementById('tech_plannig_requests').contentWindow.loadXMLDoc2('result');
                getResult();
            };

        }


        function show_hint(id_person, ym) {
            dialogPersonalPrices(id_person, ym);
        }

        function show_daily_graph(id_person, nDate, element) {
            var target = 'ApiLiftkom.getTechDailyDataForDay',
                params = [{tech_id:id_person, forDate: nDate}],
                $elem = jQuery(element);

            jQuery.ajax({
                type: 'post',
                url: 'dashboard/api.php',
                async: true,
                data:{
                    requests:JSON.stringify([{target: target, params: params}])
                },
                dataType:'json'
            }).done(function(data) {

                hs.htmlExpand(null, {
                    anchor: 'left top',
                    pageOrigin: {
                        x: $elem.position().left + 20,
                        y: $elem.position().top + 20
                    },
                    headingText: moment.unix(nDate).format('DD-MM-YYYYг.'),
                    maincontentText: '<div id="hc_daily_show"></div>',
                    width: $elem.parents('tr').width() * 0.6,
                    height: 180
                }, {date: nDate, person: id_person, data:data[0]['response']});

            }).fail(function() {
                console.log( 'fail' );
            });

            return false;
        }

        // /**
        //  * Премахва графиката от попъпите. Ако не се премахне остава в глобалния обект Highcharts
        //  */
        // function deleteChartFromExpand () {
        //     var c = jQuery("[id^='hc_daily_show']");
        //     if(c.length) {
        //         var chart = c.highcharts();
        //         if (chart) {
        //             chart.destroy();
        //         }
        //         c.remove();
        //     }
        //
        // }

        /**
         * Графиката в попъпа с информацията за детайлото движение
         */
        // function addChartToExpand ( sender) {
        //     var data = sender.custom.data, oSeries = {}, series = [];
        //
        //     for(var i in data) {
        //         if(data.hasOwnProperty(i)) {
        //             if(! oSeries.hasOwnProperty(data[i].type) ) {
        //                 oSeries[data[i].type] = {name: data[i].name, color: data[i].color, data: []}
        //             }
        //
        //             var sNote = (data[i]['type'] == 'to_layer')? " <b>Забележка</b>: "+ data[i]['move_note'] : '';
        //
        //             oSeries[data[i].type].data.push({
        //                 x: 0,
        //                 o_name: data[i]['o_name'] +sNote,
        //                 low: moment.utc(data[i]['t_from']).valueOf(),
        //                 high: moment.utc(data[i]['t_to']).valueOf()
        //             })
        //         }
        //     }
        //
        //     for(i in oSeries) {
        //         if(oSeries.hasOwnProperty(i)){
        //             series.push(oSeries[i]);
        //         }
        //     }
        //
        //     jQuery("#hc_daily_show").highcharts({
        //         chart: {
        //             type: 'columnrange',
        //             inverted: true,
        //             height: 120,
        //             spacingBottom: 5
        //         },
        //         noData: {
        //             style: {
        //                 fontWeight: 'bold',
        //                 fontSize: '15px',
        //                 color: '#303030'
        //             }
        //         },
        //         title: {
        //             text: null
        //         },
        //         credits: false,
        //         lang: {
        //             noData: 'Нама данни за показване',
        //             contextButtonTitle: 'Опции на графиката',
        //             downloadJPEG: 'Изтегли JPEG изображение',
        //             downloadPNG: 'Изтегли PNG изображение',
        //             downloadSVG: 'Изтегли SVG изображение',
        //             downloadPDF: 'Изтегли PDF документ',
        //             printChart: 'Принтирай графиката',
        //             loading: 'Зареждане',
        //             months: ["Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември"],
        //             shortMonths: ["Ян", "Фев", "Мар", "Апр", "Май", "Юни", "Юли", "Авг", "Сеп", "Окт", "Ное", "Дек"],
        //             weekdays: ["Неделя", "Понеделник", "Вторник", "Сряда", "Четвъртък", "Петък", "Събота"]
        //         },
        //         columnrange: {
        //             pointInterval: 2 * 3600 * 1000
        //         },
        //         exporting: {
        //             enabled: false
        //         },
        //         xAxis: {
        //             categories: ['dates'],
        //             labels: {
        //                 enabled: false
        //             }
        //         },
        //         yAxis: {
        //             type: 'datetime',
        //             labels: {
        //                 formatter: null
        //             },
        //             dateTimeLabelFormats: { //force all formats to be hour:minute:second
        //                 hour: '%H:%M',
        //                 day: '%H:%M'
        //             },
        //             title: {
        //                 text: null
        //             },
        //             startOnTick: true
        //         },
        //         plotOptions: {
        //             columnrange: {
        //                 grouping: false
        //             },
        //             series: {
        //                 stacking: false
        //             }
        //         },
        //         tooltip:{
        //             formatter: function() {
        //                 return '<b>' + this.series.name + '</b> - ' + this.point.o_name + '<br/>'+
        //                     Highcharts.dateFormat('%H:%M',this.point.low ) + ' - ' +Highcharts.dateFormat('%H:%M',this.point.high)+'<br/>';
        //             }
        //         },
        //         series: series
        //     });
        // }

        rpc_on_exit = function ( nCode ) {
            if( !parseInt( nCode ) ) {
                document.getElementById('OnlyTecnicks').focus();
                if( $('start').value != '0' ) {
                    $('start').value = '0';
                    $('end').value = '0';
                    if($('id_request_from_contract').value == 0) {
                        var id_office = parent.document.getElementById('id_request_office').value;
                        parent.document.getElementById('tech_plannig_requests').src = 'page.php?page=tech_planning_requests&id_office='+id_office;
                    }
                }
            }
        };


        function hideData() {
            var parent_id = parent.document.getElementById('set_setup_tech_request');
            if(parent_id)
                jQuery('table.hide_element').hide(0);
        }
        jQuery(document).ready(function() {
            jQuery('#check_btn').on('click',function(){
                var chkBox = jQuery('#OnlyTecnicks');
                chkBox.prop("checked", !chkBox.prop("checked"));
                getResult();
            });
        });

    </script>

    <style>
        .t_full {
            padding-left: 0;
        }
        .t_full .input-group-sm {
            width: 100% !important;
        }
        .label {
            width: 50px;
            padding-right: 5px;
        }
        td.page_caption {
            border: none;
        }
        button.saveplan {
            color:#ffffff;
            background:#612c2c;
            font:16px verdana;
        }

        table.result td.person_on_duty a:link,
        table.result td.person_on_duty a:visited {
            color:green;
            text-decoration: none;
            font-weight : bold;
        }
        table.result td.person_on_leave a:link,
        table.result td.person_on_leave a:visited {
            color:red;
            text-decoration: none;
            font-weight : bold;
        }
        .real_graph {
            text-align: center;
            color: #FD9C28;
        }
        .real_graph i.glyphicon {
            width: 15px;
        }
        .real_graph i:hover {
            cursor: pointer;
            font-size: 14px;
        }
        #row {
            font-size: 14px;
            font-weight: bold;
            color: #555555;
            height: 34px;
            padding: 4px;
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
            background-color: #f5f5f5;
            background-image: -moz-linear-gradient(top, #ffffff, #e6e6e6);
            background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), to(#e6e6e6));
            background-image: -webkit-linear-gradient(top, #ffffff, #e6e6e6);
            background-image: -o-linear-gradient(top, #ffffff, #e6e6e6);
            background-image: linear-gradient(to bottom, #ffffff, #e6e6e6);
            background-repeat: repeat-x;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffe6e6e6', GradientType=0);
            border-color: #e6e6e6 #e6e6e6 #bfbfbf;
            border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
            filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
            border: 1px solid #cccccc;
            border-bottom-color: #b3b3b3;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
            -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
            margin-bottom: 2px;
        }
        .open-team {
            cursor: pointer;
        }
    </style>
{/literal}

<dlcalendar click_element_id="imgDate" input_element_id="date" tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;" class="form-horizontal" role="form">
    <input type="hidden" name="start" id="start" value="0">
    <input type="hidden" name="end" id="end" value="0">
    <input type="hidden" name="id_request" id="id_request" value="0">
    <input type="hidden" name="id_request_from_contract" id="id_request_from_contract" value="{$nIDRequest|default:0}">

    <div class="row"  id="row">
        <div class="col-lg-1 hidden-xs">
            Планиране
        </div>

        <div class="col-lg-2 col-xs-3">
            <div class="input-group input-group-sm">
                <button class="btn btn-xs btn-info" type="button" onClick="open_tech_teams();"><span class="fa fa-users"></span> Екипи &nbsp; </button>
                &nbsp;
                <span class="input-group input-group-addon">
                    <input type="checkbox" checked="checked" name="OnlyTecnicks" id="OnlyTecnicks" onClick="getResult();"/>
                    Техници
                </span>
            </div>
        </div>

        <!-- край техници -->
        <div class="col-lg-2 col-xs-2 t_full">
            <div class="input-group input-group-sm">
                <span class="input-group-addon glyphicon glyphicon-file"></span>
                <select class="form-control" id="nIDOffice" name="nIDOffice" onchange="getResult();" ></select>
            </div>
        </div>

        {* INFO: 26.07.2016 - Скриваме временно селекта за да видим дали ще липсва на някой :) *}
        {*<div class="col-lg-2 col-xs-2 t_full">*}
        {*<div class="hide_element">*}
        {*<!-- Обслужване -->*}
        {*<div class="input-group input-group-sm">*}
        {*<span class="input-group-addon glyphicon glyphicon-tags" ></span>*}
        {*<table>*}
        {*<select class="form-control" name="lc_type" id="lc_type" onchange="getResult();">*}
        {*<option value="">-- Всички --</option>*}
        {*{foreach from=$aTechTiming item=aType}*}
        {*<option value="{$aType.id}">{$aType.description}</option>*}
        {*{/foreach}*}
        {*</select>*}
        {*</div>*}
        {*</div>*}
        {*</div>*}

        <div class="col-lg-2 col-xs-1 t_full">
            <div class="input-group input-group-sm">
                <span class="input-group-addon glyphicon glyphicon-list-alt" ></span>
                <select class="form-control" name="type" id="type" onchange="changeType(this.value)">
                    <option value="day">Дневен</option>
                    <option value="month">Месечен</option>
                </select>
            </div>
        </div>
        <div class="col-lg-3 col-xs-3">
            <div id="pDay">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon glyphicon glyphicon-chevron-left" onclick="nextDate('prev');" ></span>
                    <span class="input-group-addon glyphicon glyphicon-calendar" id="imgDate" ></span>
                    <input class="form-control form-control-calendar" id="date" name="date" type="text" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" />
                    <span class="input-group-addon glyphicon glyphicon-chevron-right" onclick="nextDate('next');" style="z-index:auto;" ></span>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-xs-2" id="pMonth">
            <div class="input-group input-group-sm">
                <span class="input-group-addon glyphicon glyphicon-chevron-left" onclick="nextMonth('prev');"></span>
                <input class="form-control form-control-calendar" id="dateM" name="dateM" type="text" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" />
                <span class="input-group-addon glyphicon glyphicon-chevron-right" onclick="nextMonth('next');" ></span>
            </div>
        </div>
        <div class="col-lg-2 col-xs-3" style="text-align: right; padding-right: 15px;">
            {if $right_edit}
                <button class="btn btn-xs btn-info" type="search" name="button" onClick="save();" ><span class="fa fa-save"></span>  Запази &nbsp; </button>
            {else}
            {/if}
            <button class="btn btn-xs btn-success" type="button" onClick="getResult();"><span class="fa fa-refresh"></span> Обнови </button>

            {* INFO: 26.07.2016 - Скриваме временно бутона за да видим дали ще липсва на някой :) *}
            {*<button class="btn btn-xs btn-primary" onclick="parent.window.location='page.php?page=tech_support_requests';" id="to_tech_request"><span class="fa fa-reply"></span> Заявки </button>*}
            <!-- Затворени карти
                <td align="right" valign="middle" width="50px">
                    <input type="checkbox" class="clear" checked="checked" name="closedLimitCards" id="closedLimitCards" onClick = "getResult();">
                </td>
                <td valign="middle" width="150px">затворени карти</td>
                -->
        </div>
    </div>

    {*<tr style="height:35px;">*}
    {*<td width="200px" class="page_caption">График</td>*}

    {*<td align="right" valign="middle" width="100px">Фирма</td>*}
    {*<td valign="middle" width="170px">*}
    {*<select style="width:150px;" class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />*}
    {*</td>*}

    {*<td align="right" valign="middle" width="50px">*}
    {*<input type="checkbox" class="clear" checked="checked" name="OnlyTecnicks" id="OnlyTecnicks" onClick = "getResult();">*}
    {*</td>*}
    {*<td valign="middle" width="150px">*}
    {*само техници*}
    {*</td>*}

    {*<td>*}
    {*<div id="pDay">*}
    {*<table>*}
    {*<tr>*}
    {*<td valign="middle">*}
    {*<button onclick="nextDate('prev');" ><img src="images/mleft.gif" /></button>*}
    {*</td>*}
    {*<td valign="middle" align="center" >*}
    {*<input style="width:70px;" id="date" name="date" type="text" class="clear" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" />*}
    {*</td>*}
    {*<td valign="middle">*}
    {*<button onclick="nextDate('next');" ><img src="images/mright.gif" /></button>*}
    {*</td>*}
    {*<td>*}
    {*<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="imgDate" />*}
    {*</td>*}
    {*</tr>*}
    {*</table>*}
    {*</div>*}

    {*<div id="pMonth">*}
    {*<table>*}
    {*<tr>*}
    {*<td valign="middle">*}
    {*<button onclick="nextMonth('prev');" ><img src="images/mleft.gif" /></button>*}
    {*</td>*}
    {*<td valign="middle" align="center">*}
    {*<input style="width:70px;" id="dateM" name="dateM" type="text" class="clear" onkeypress="return formatDate(event, '.');" maxlength="10" readonly title="ДД.ММ.ГГГГ" />*}
    {*</td>*}
    {*<td valign="middle">*}
    {*<button onclick="nextMonth('next');" ><img src="images/mright.gif" /></button>*}
    {*</td>*}
    {*</tr>*}
    {*</table>*}
    {*</div>*}
    {*</td>*}

    {*<td style="width:200px;" align="center" >*}
    {*<button onclick="parent.window.location='page.php?page=tech_support_requests';">Към заявки</button>*}
    {*</td>*}
    {*<td>*}
    {*&nbsp;*}
    {*</td>*}
    {*</tr>*}

    {*<tr style="height:30px;" class="hide_element">*}
    {*<td align="center" width="250px">*}
    {*<table class="input">*}
    {*<tr>*}
    {*<td align="right" nowrap>Тип:&nbsp;</td>*}
    {*<td align="left">*}
    {*<select style="width:120px;" name="type" id="type" onchange="changeType(this.value)">*}
    {*<option value="day">Дневен</option>*}
    {*<option value="month">Месечен</option>*}
    {*</select>*}
    {*</td>*}
    {*</tr>*}
    {*<tr>*}
    {*<td align="right" nowrap>Вид операция:&nbsp;</td>*}
    {*<td align="left">*}
    {*<select style="width: 120px;" name="lc_type" id="lc_type" onchange="getResult();">*}
    {*<option value="">-- Всички --</option>*}
    {*<option value="create">Изграждане</option>*}
    {*<option value="destroy">Снемане</option>*}
    {*<option value="holdup">Профилактика</option>*}
    {*<option value="arrange">Аранжиране</option>*}
    {*</select>*}
    {*</td>*}
    {*</tr>*}
    {*</table>*}
    {*</td>*}
    {*<td align="right" valign="middle" width="100px">Регион</td>*}
    {*<td valign="middle" width="170px">*}
    {*<select style="width:150px;" class="default" name="nIDOffice" id="nIDOffice" onchange="getResult();" />*}
    {*</td>*}
    {*<td align="right" valign="middle" width="50px">*}
    {*<input type="checkbox" class="clear" checked="checked" name="closedLimitCards" id="closedLimitCards" onClick = "getResult();">*}
    {*</td>*}
    {*<td valign="middle" width="150px">*}
    {*затворени карти*}
    {*</td>*}
    {*<td colspan="1" align="center" width="105px">*}
    {*<button type="button" onClick="getResult();" class="search"><img src="images/reload.gif" />Обнови</button>*}
    {*</td>*}
    {*<td colspan="1" width="200px" align="center">*}
    {*{if $right_edit}*}
    {*<button class="saveplan" type="button" name="button" onClick="save();" ><b>Запази</b></button>*}
    {*{else}&nbsp;*}
    {*{/if}				*}
    {*</td>*}
    {*<td >*}
    {*&nbsp;*}
    {*</td>*}
    {*</tr>*}


    <div id="result" style="height: 90%;" rpc_excel_panel="off" rpc_resize="off"></div>

    {if $nIDRequest}
        {if $nPicNum}
            Приблизително {$nHours} час(а)
            {if $nMinute}
                и {$nMinute} минути
            {/if}
            <img src="images/time/red{$nPicNum}.gif" />
        {else}
            {$sObjectName} {$sTechType}
        {/if}

    {/if}

</form>

<script>
    onInit();
</script>