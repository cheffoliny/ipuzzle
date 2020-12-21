/**
 * Различните типове графики за Highcharts
 */

//Месечна справка
function drawPolarInverted(chartData) {
    var dfd = jQuery.Deferred(),
        tt = "нама данни",
        chart,
        container = jQuery('#chart-container'),
        container_h = container.height(),
        h = parseInt(chartData['results_cnt'], 10) * 30,  //по 30px на ред
        inner = jQuery('<div/>', {
            'id': 'polar-inverted'
        }).appendTo(container),
        cd = chartData['service_data'];

    if(container_h < h) {
        inner.slimScroll({
            height: container_h + 'px',
            size: '15px'
        })
    }

    if(chartData['total_all']) {
        tt = Highcharts.numberFormat(chartData['total_all'], cd['decimal_format'], '.', ' ');
    }

    var options = {
        chart: {
            renderTo: 'polar-inverted',
            borderWidth: 1,
            spacingBottom: 50,
            inverted: true,
            height: (container_h < h) ? h : container_h
        },

        title: {
            text: chartData['chartTitleData']['groupParam'] + ' ' +
                chartData['chartTitleData']['periodStr'] + ' (' + cd['group_suffix'] + '.)',
            x: -20
        },

        xAxis: {
            reversed: false,        //TODO: Дали потребителя ще е най-отгоре
            staggerLines: 2,
            categories: chartData.names
        },

        yAxis: [{
            minRange: cd['chart_min_range'] || 5,
            //                max: chartData['service_data']['chart_max_value']
            title: {
                text: cd['group_suffix']
            }
        },
            {
                linkedTo: 0,
                opposite: true,
                gridLineWidth: 0,
//                max: chartData['service_data']['chart_max_value']
                title: {
                    text: cd['group_suffix']
                }
            }],

        series: [
            {
                type: 'line',
                name: chartData['chartTitleData']['groupParam'],
                data: chartData['vals'],
                pointPlacement: 'on'

            }
        ],

        tooltip: {
//            headerFormat: '',
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: {point.y:,.'+ cd['decimal_format'] +'f}' + cd['group_suffix']
        },

        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
//            y: 50,
            borderWidth: 0
        },

        labels: {
            items: [
                {
                    html: 'Общо ' + chartData['chartTitleData']['group'] + ': <b>' + chartData['results_cnt'] + ' бр.</b>',
                    style: {
                        top: '-65px',
                        left: '-100px'
                    }
                },
                {
                    html: 'Общо ' + chartData['chartTitleData']['groupParam'] +': <b>' + tt + " " + cd['group_suffix'] + '.</b>',
                    style: {
                        top: '-45px',
                        left: '-100px'
                    }
                }
            ],
            style: {
                color: '#3E576F'
            }
        }
    };

    chart = new Highcharts.Chart(options);

    if(cd['chart_min_range'] && chartData['chartViewType'].toString().indexOf('dealers') != -1 && !chartData['params'] ) {
        chart.yAxis[0].addPlotLine({
            value: chartData['service_data']['chart_min_range'],
            color: 'red',
            width: 2,
            id: 'plot-line-dealers'
        });
        chart.addSeries({
            name: 'лимит ' + Highcharts.numberFormat(cd['chart_min_range'], cd['decimal_format'], '.', '') + ' ' + cd['group_suffix'] + '.',
            color: 'red',
            events: {
                legendItemClick: function() {
                    return false;
                }
            }
        });
    }

    dfd.resolve();
    return dfd.promise();
}


/**
 *
 */
function drawPolar(chartData) {
    var dfd = jQuery.Deferred(),
        tt = "нама данни",
        chart,
        cd = chartData['service_data'],
        additional = '';

//    if(cd.hasOwnProperty('additional_data') && cd['additional_data'].length > 0) {
//        if(typeof cd['additional_data'] === 'object') {
//            for(var i in cd['additional_data']) {
//                if(cd['additional_data'].hasOwnProperty(i))
//                    additional += '<br><span>' + cd['additional_data'][i].name + ' - ' + cd['additional_data'][i].value + cd['group_suffix'] + '</span>'
//            }
//        }
//    }

    if(chartData['total_all']) {
        tt = Highcharts.numberFormat(chartData['total_all'], cd['decimal_format'], '.', ' ');
    }

    var options = {
        chart: {
            renderTo: 'chart-container',
            polar: true,
            type: 'line'
        },

        title: {
            text: chartData['chartTitleData']['groupParam'] + ' ' +
                chartData['chartTitleData']['periodStr'] + ' (' + cd['group_suffix'] + '.)'
        },

        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: {point.y:,.'+ cd['decimal_format'] +'f}' + cd['group_suffix'] + additional
//                    formatter: function() {
//                        var s = '<b>'+ this.x +'</b>';
//                        console.log( this, 'this' );
//
//                        jQuery.each(this.points, function(i, point) {
//
//                            console.log( chartData, 'zgfdff' );
//                            if(chartData['offices']) {
//                                var a = chartData['offices'][point.key] || '';
//                                s += '<br/>' + a + '<br/>' + point.y
//                            }
//                        });
//
//                        return s;
//                    }
//                    shared: true
        },

        pane: {
//            size: '100%'
        },

        xAxis: {
            categories: chartData.names,
            tickmarkPlacement: 'on',
            lineWidth: 0,
            labels: {
                formatter: function() {
                    var a = this.value.toString().toLowerCase().localeCompare(cd['boldName'].toLowerCase());
                    if(a === 0) {
                        return '<b>' + this.value + '</b>';
                    }
                    else {
                        return this.value;
                    }
                },
                staggerLines: 2,
                y: 0
            }
        },

        yAxis: {
            lineWidth: 0,
            minTickInterval: 1,
            minRange: cd['chart_min_range'] || 5,
//            max: chartData['service_data']['chart_max_value'],
            labels: {
                formatter: function () {
                    return Highcharts.numberFormat(this.value, cd['decimal_format'], '.', ' ');
                }
            }
        },

        series: [{
            name: chartData['chartTitleData']['groupParam'],
            data: chartData['vals'],
            pointPlacement: 'on'
        }],

        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            borderWidth: 0,
            y: 50
        },

        labels: {
            items: [
                {
                    html: 'Общо ' + chartData['chartTitleData']['group'] + ': <b>' + chartData['results_cnt'] + ' бр.</b>',
                    style: {
                        top: '0px',
                        left: '20px'
                    }
                },
                {
                    html: 'Общо ' + chartData['chartTitleData']['groupParam'] +': <b>' + tt + " " + cd['group_suffix'] + '.</b>',
                    style: {
                        top: '20px',
                        left: '20px'
                    }
                }
            ],
            style: {
                color: '#3E576F'
            }
        }
    };

    chart = new Highcharts.Chart(options);
//    if(chart.hasData()) {
//        chart.yAxis[0].setExtremes(0, chartData['service_data']['chart_max_value'], true);
//
//    }
    if(cd['chart_min_range'] && chartData['chartViewType'].toString().indexOf('dealers') != -1 && !chartData['params'] ) {
        chart.yAxis[0].addPlotLine({
            value: cd['chart_min_range'],
            color: 'red',
            width: 2,
            id: 'plot-line-dealers'
        });
        chart.addSeries({
            name: 'лимит '+ Highcharts.numberFormat(cd['chart_min_range'], cd['decimal_format'], '.', '') +' '+ cd['group_suffix'] + '.',
            color: 'red',
            events: {
                legendItemClick: function() {
                    return false;
                }
            }
        });
    }

    dfd.resolve();
    return dfd.promise();
}


/**
 *
 */
function drawVertical(chartData) {

    var dfd = jQuery.Deferred(),
        cd = chartData['service_data'],
        periodLength = chartData['monthLabels'].length;

    var isDealer = (cd['chart_min_range'] && chartData['chartViewType'].toString().indexOf('dealers') != -1 && !chartData['params']);
    //За сега го смятам тук, ако се поставят различни цели за всеки отделен търговец ще трябва да се смята в цикъла
    var periodGoal = (isDealer && periodLength > 1) ? ' Цел за периода: ' + Highcharts.numberFormat( (parseInt(cd['chart_min_range'], 10) * periodLength), cd['decimal_format'], '.', '') + ' '+ cd['group_suffix'] + '.' : '';

    if(chartData.names.length) {
        for( var i=0; i<chartData.names.length; i++ ) {

            var container_name = 'inner-container-' + i,
                region = chartData['service_data']['additional_data'][i]['office']['value'] || '';

            jQuery('#chart-container').append('<div id="'+container_name+'"></div>');

            var options = {
                chart: {
                    renderTo: container_name,
                    borderWidth: 1,
                    spacingBottom: 50,
                    height: 250
                },

                title: {
                    text: chartData.names[i] + ' - ' + chartData['chartTitleData']['groupParam'] + ' ' + chartData['chartTitleData']['periodStr'],
                    x: -20
                },

                subtitle: {
                    text: 'Общо '+ chartData['chartTitleData']['groupParam'] +
                        ' за периода: <b>' + Highcharts.numberFormat(chartData['totals'][i], cd['decimal_format'], '.', ' ' ) +' '+ cd['group_suffix'] +'.</b>' + periodGoal
                },

                xAxis: {
                    categories: chartData['monthLabels'],
                    labels: {
                        y: 20
                    }
                },

                yAxis: {
                    title: {
                        text: '<b>' + chartData.names[i] + '</b><br/>' + region,
                        margin: 80,
                        x: 20,
                        style: {
                            fontWeight: 'normal'
                        }
                    },
                    minRange: cd['chart_min_range'] || 5,
//                    max: chartData['service_data']['chart_max_value'],
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },

                series: [{
                    name: chartData['chartTitleData']['groupParam'],
                    data: chartData['vals'][i],
                    pointPlacement: 'on'
                }],

                tooltip: {
                    pointFormat: '<span style="color:{series.color}">{series.name}</span>: {point.y:,.'+ cd['decimal_format'] +'f}' + cd['group_suffix']
                },

                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                }
            };

            var chart = new Highcharts.Chart(options);
            if( isDealer ) {
                chart.yAxis[0].addPlotLine({
                    value: cd['chart_min_range'],
                    color: 'red',
                    width: 2,
                    id: 'plot-line-dealers'
                });
                chart.addSeries({
                    name: 'лимит '+ Highcharts.numberFormat(cd['chart_min_range'], cd['decimal_format'], '.', '') + ' '+ cd['group_suffix'] + '.',
                    color: 'red',
                    events: {
                        legendItemClick: function() {
                            return false;
                        }
                    }
                }, false, false);
            }
        }
    }
    else {
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'chart-container'
            },
            title: {
                text: chartData['chartTitleData']['groupParam'] + ' ' + chartData['chartTitleData']['periodStr']
            },
            series: [{
                type: 'line',
                name: chartData['chartTitleData']['groupParam'],
                data: []
            }],
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            yAxis: {
                title: {
                    text: null
                }
            }
        });
    }

    dfd.resolve();
    return dfd.promise();
}


function drawVerticalDayType(chartData) {

    var dfd = jQuery.Deferred(),
        cd = chartData['service_data'];

    if(chartData.names.length) {

        for( var i=0; i<chartData.names.length; i++ ) {

            var container_name = 'inner-container-' + i,
                region = chartData['service_data']['additional_data'][i]['office']['value'] || '';

            jQuery('#chart-container').append('<div id="'+container_name+'"></div>');

            var options = {
                chart: {
                    renderTo: container_name,
                    borderWidth: 1,
                    spacingBottom: 50,
                    height: 250
                },

                title: {
                    text: chartData.names[i] + ' - ' + chartData['chartTitleData']['groupParam'] + ' ' + chartData['chartTitleData']['periodStr'],
                    x: -20
                },

//                subtitle: {
//                    text: 'Общо '+ chartData['chartTitleData']['groupParam'] +
//                        ' за периода: <b>' + Highcharts.numberFormat(chartData['total_sum'][i], chartData['service_data']['decimal_format'], '.', ' ' ) +
//                        ' '+ chartData['service_data']['group_suffix'] +'.</b>'
//                },

                xAxis: {
                    type: 'datetime',
                    max: moment.utc().endOf('month').valueOf(),
//                    endOnTick: true,
                    showLastLabel: true,
                    labels: {
                        y: 20
                    }
                },

                yAxis: {
                    title: {
                        text: '<b>' + chartData.names[i] + '</b><br/>' + region,
                        margin: 80,
                        x: 20,
                        style: {
                            fontWeight: 'normal'
                        }
                    },
                    minRange: cd['chart_min_range'] || 5,
//                    max: chartData['service_data']['chart_max_value'],
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },

                series: [{
                    name: chartData['chartTitleData']['groupParam'],
                    data: chartData['vals'][i],
                    pointPlacement: 'on',
                    pointStart: moment.utc().startOf('month').valueOf(),
                    pointInterval: 24 * 3600 * 1000,
                    zIndex: 1,
                    type: 'line'
                }],

                tooltip: {
                    pointFormat: '<span style="color:{series.color}">{series.name}</span>: {point.y:,.'+ cd['decimal_format'] +'f}' + cd['group_suffix']
                },

                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                }
            };

            var chart = new Highcharts.Chart(options);

            if(cd['chart_min_range'] && chartData['chartViewType'].toString().indexOf('dealers') != -1 && !chartData['params'] ) {
                chart.addSeries({
                    name: 'лимит '+ Highcharts.numberFormat(cd['chart_min_range'], cd['decimal_format'], '.', '') + ' ' + cd['group_suffix'] + '.',
                    data:[[moment.utc().startOf('month').valueOf(), 0],[moment.utc().endOf('month').valueOf(), cd['chart_min_range']]],
                    color: '#FF0000',
                    showInLegend: true,
                    events: {
                        legendItemClick: function() {
                            return false;
                        }
                    }
                });
            }
        }
    }
    else {
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'chart-container'
            },
            title: {
                text: chartData['chartTitleData']['groupParam'] + ' ' + chartData['chartTitleData']['periodStr']
            },
            series: [{
                type: 'line',
                name: chartData['chartTitleData']['groupParam'],
                data: []
            }],
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            yAxis: {
                title: {
                    text: null
                }
            }
        });
    }

    dfd.resolve();
    return dfd.promise();
}
