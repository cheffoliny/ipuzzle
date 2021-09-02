<?php

if( $_SESSION['access'] != 'admin' && strpos($_SESSION['access'],'imonitor_main_ireport') === false ) {
    echo  'Нямате права за достъп'; die();
}

?>
    <section class="content">
    <!--<a class="btn btn-block btn-default" data-toggle="modal" data-target="#compose-modal" style="padding-top: 10px; padding-bottom: 10px;"><i class="fa fa-pencil"></i> Заявка </a>-->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12 connectedSortable">
                <!-- Box (with bar chart) -->
                <div class="box box-danger" id="loading-example">
                    <div class="box-header">
                        <!-- tools box -->
                        <div class="pull-right box-tools">
                            <button class="btn btn-default btn-sm refresh-btn" data-toggle="tooltip" title="Reload"><i class="fa fa-refresh"></i></button>
                            <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
                        </div><!-- /. tools -->
                        <i class="fa fa-bell red"></i>

                        <h3 class="box-title">Аларми</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body no-padding">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <!-- bar chart -->
                                <div class="chart" id="iii-chart" style="height: 300px;">
                                    <svg id="alarms_by_day" class="col-sm-12" version="1.1" xmlns="http://www.w3.org/2000/svg" style="height: 300px; overflow: hidden; position: relative;">
                                    </svg>
                                </div>
                            </div>
                            <div class="col-md-3 col-xs-6">
                                <div class="pad" id="alarms_by_type">
                                    <!-- Progress bars -->

                                </div><!-- /.pad -->
                            </div><!-- /.col -->
                            <div class="col-md-3 col-xs-6">
                                <div class="pad" id="alarms_by_signal">
                                    <!-- Progress bars -->

                                </div><!-- /.pad -->
                            </div><!-- /.col -->
                        </div><!-- /.row - inside box -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </section><!-- left col -->
        </div><!-- /.row (main row) -->


        <div class="row">
            <section class="col-lg-12 connectedSortable">
                <!-- Box (with bar chart) -->
                <div class="box box-danger">
                    <div class="box-header">

                        <i class="fa fa-bar-chart-o"></i>
                        <input type="hidden" id="start_date"  name="start_date" value="" disabled="disabled" />
                        <input type="hidden" id="end_date"    name="end_date"   value="" disabled="disabled" />
                        <input type="hidden" id="office"      name="office"     value="0" disabled="disabled" />
                        <h3 class="box-title">За
                            <span class="badge bg-green">
                                <input type="text" id="diff_interval" name="diff_interval" value="" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 25px; font-size: 10px;" />
                            </span> дни и над
                            <span class="badge bg-red">
                                <input type="text" id="count_alarms" name="count_alarms"  value="1" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 20px; font-size: 10px;" /></span>
                            <span class="badge bg-blue">
                                <input type="text" id="type_alarms" name="type_alarms"    value="real" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 25px; font-size: 10px;" /></span> аларми
                        </h3>

                        <div class="pull-right btn-group">
                            <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bell-o"></i></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a> Сигнали:  </a></li>
                                <li class="divider"></li>
                                <li><a id="visited" onclick="changeTypeAlarms('visited' ); alarms_by_object(); return false;"><i class="fa fa-cab"   ></i> Посетени   </a></li>
                                <li><a id="real"    onclick="changeTypeAlarms('real'    ); alarms_by_object(); return false;"><i class="fa fa-bell-o"></i> Реални     </a></li>
                                <li><a id="tech"    onclick="changeTypeAlarms('tech'    ); alarms_by_object(); return false;"><i class="fa fa-wrench"></i> Технически </a></li>
                                <li><a id="all"     onclick="changeTypeAlarms('all'     ); alarms_by_object(); return false;"><i class="fa fa-bell-o"></i> Всички     </a></li>
                            </ul>
                            <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                        </div>

                        <div class="pull-right btn-group">
                            <button aria-expanded="true" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                &nbsp; РЕГИОН &nbsp;  <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="#" onclick="changeOffice(0); alarms_by_object(); return false;">Всички</a></li><li class="divider"></li>
                                <?php getOffices(); ?>
                            </ul>
                        </div>
                        <div class="pull-right btn-group">
                            <div class="input-group">
                                <div id="reportrange" class="btn btn-default btn-sm pull-right">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </div>
                        </div><!-- /.form group -->

                    </div><!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table class="table table-condensed table-striped"  id="alarms_by_object">

                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </section><!-- /.col -->
        </div>


        <div class="row">
            <section class="col-lg-12 connectedSortable">
            <!-- Box (with bar chart) -->
            <div class="box box-danger">
                <div class="box-header">

                    <i class="fa fa-bar-chart-o"></i>
                    <input type="hidden" id="start_date_d"  name="start_date_d" value="" disabled="disabled" />
                    <input type="hidden" id="end_date_d"    name="end_date_d"   value="" disabled="disabled" />
                    <input type="hidden" id="office_d"      name="office_d"     value="0" disabled="disabled" />
                    <h3 class="box-title">За
                        <span class="badge bg-green">
                                <input type="text" id="diff_interval_d" name="diff_interval_d" value="" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 25px; font-size: 10px;" />
                            </span> дни и над
                        <span class="badge bg-red">
                                <input type="text" id="count_alarms_d" name="count_alarms_d"  value="0" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 20px; font-size: 10px;" /></span>
                        <span class="badge bg-blue">
                                <input type="text" id="type_alarms_d" name="type_alarms_d"    value="real" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 25px; font-size: 10px;" /></span> аларми
                    </h3>

                    <div class="pull-right btn-group">
                        <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bell-o"></i></button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a> Сигнали:  </a></li>
                            <li class="divider"></li>
                            <li><a id="visited_d" onclick="changeTypeAlarms_d('visited' ); alarms_by_object_detailed(); return false;"><i class="fa fa-cab"   ></i> Посетени   </a></li>
                            <li><a id="real_d"    onclick="changeTypeAlarms_d('real'    ); alarms_by_object_detailed(); return false;"><i class="fa fa-bell-o"></i> Реални     </a></li>
                            <li><a id="tech_d"    onclick="changeTypeAlarms_d('tech'    ); alarms_by_object_detailed(); return false;"><i class="fa fa-wrench"></i> Технически </a></li>
                            <li><a id="all_d"     onclick="changeTypeAlarms_d('all'     ); alarms_by_object_detailed(); return false;"><i class="fa fa-bell-o"></i> Всички     </a></li>
                        </ul>
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>

                    <div class="pull-right btn-group">
                        <button aria-expanded="true" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            &nbsp; РЕГИОН &nbsp;  <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" onclick="changeOffice_d(0); alarms_by_object_detailed(); return false;">Всички</a></li><li class="divider"></li>
                            <?php getOffices_d(); ?>
                        </ul>
                    </div>
                    <div class="pull-right btn-group">
                        <div class="input-group">
                            <div id="reportrange_d" class="btn btn-default btn-sm pull-right">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                <span></span> <b class="caret"></b>
                            </div>
                        </div>
                    </div><!-- /.form group -->

                </div><!-- /.box-header -->
                <div class="box-body no-padding">
                    <table class="table table-condensed table-striped" id="alarms_by_object_detailed">

                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </section><!-- /.col -->
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header">
                        <i class="fa fa-clock-o"></i>
                        <h3 class="box-title">Закъснения при реакция</h3>
                        (<input type="text" id="time_interval" name="time_interval" value="2" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 20px;" /> дни)
                        (<input type="text" id="time_delay"    name="time_delay"    value="end" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 23px;" />)

                        <div class="box-tools pull-right">
                            <div class="btn-group">
                                <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-clock-o"></i></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a> Период  </a></li>
                                    <li class="divider"></li>
                                    <li><a id="2"  onclick="changeTimeInterval(2); alarms_by_delay(); return false;">2 дни  </a></li>
                                    <li><a id="10" onclick="changeTimeInterval(10); alarms_by_delay(); return false;">10 дни</a></li>
                                    <li><a id="20" onclick="changeTimeInterval(20); alarms_by_delay(); return false;">20 дни</a></li>
                                    <li><a id="30" onclick="changeTimeInterval(30); alarms_by_delay(); return false;">30 дни</a></li>
                                </ul>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-hourglass-end"></i></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a> Закъснение при:  </a></li>
                                    <li class="divider"></li>
                                    <li><a id="send"   onclick="changeTimeDelay('send'); alarms_by_delay(); return false;">Оповестяване</a></li>
                                    <li><a id="start"  onclick="changeTimeDelay('start'); alarms_by_delay(); return false;">Приемане    </a></li>
                                    <li><a id="end"    onclick="changeTimeDelay('end'); alarms_by_delay(); return false;">Отработване </a></li>
                                </ul>
                            </div>
                            <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table class="table table-condensed"  id="alarms_by_delay">

                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row-->
    </section>

    <!-- page script -->
    <script type="text/javascript">
        function drawDocSparklines() {
            // Bar charts using inline values
            $(".sparkbar").sparkline("html", {type: "bar"});
        }

        function changeOffice(value) {
            document.getElementById('office').value = value;
        }

        function changeOffice_d(value) {
            document.getElementById('office_d').value = value;
        }

        function changeTimeInterval(value) {
            document.getElementById('time_interval').value = value;
        }

//        function changeTimeInterval_2(value) {
//            document.getElementById('time_interval_2').value = value;
//            document.getElementById('count_alarms').value = Math.round( value / 3 );
//        }

        function changeTimeDelay(value) {
            document.getElementById('time_delay').value = value;
        }

        function changeTypeAlarms(value) {
            document.getElementById('type_alarms').value = value;
        }

        function changeTypeAlarms_d(value) {
            document.getElementById('type_alarms_d').value = value;
        }
    </script>
    <!-- Page script -->
    <script type="text/javascript">
        $(function() {

            var start = moment().subtract(1, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
                document.getElementById('start_date').value = start.format('DD.MM.YYYY');
                document.getElementById('end_date').value = end.format('DD.MM.YYYY');

                var diff_interval = end.diff(start, 'days');
                document.getElementById('diff_interval').value = diff_interval;
                document.getElementById('count_alarms').value = Math.round( diff_interval / 3 ); // SET value for alarms counter
                //alert("You are " + diff_interval + " years old.");
                alarms_by_object(); return false;
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    '24 часа':[moment().subtract(1, 'days'), moment()],
                    '7 дни':  [moment().subtract(7, 'days'), moment()],
                    '10 дни': [moment().subtract(10, 'days'), moment()],
                    '20 дни': [moment().subtract(20, 'days'), moment()],
                    '30 дни': [moment().subtract(30, 'days'), moment()],
                    'Този месец': [moment().startOf('month'), moment().endOf('month')],
                    'Преден месец': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }

            }, cb);

            cb(start, end);

            function cb_d(start, end) {
                $('#reportrange_d span').html(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
                document.getElementById('start_date_d').value = start.format('DD.MM.YYYY');
                document.getElementById('end_date_d').value = end.format('DD.MM.YYYY');

                var diff_interval_d = end.diff(start, 'days');
                document.getElementById('diff_interval_d').value = diff_interval_d;
                document.getElementById('count_alarms_d').value = 0; // All alarms
                //alert("You are " + diff_interval + " years old.");
                alarms_by_object_detailed(); return false;
            }

            $('#reportrange_d').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    '24 часа':[moment().subtract(1, 'days'), moment()],
                    '7 дни':  [moment().subtract(7, 'days'), moment()],
                    '10 дни': [moment().subtract(10, 'days'), moment()],
                    '20 дни': [moment().subtract(20, 'days'), moment()],
                    '30 дни': [moment().subtract(30, 'days'), moment()],
                    'Този месец': [moment().startOf('month'), moment().endOf('month')],
                    'Преден месец': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }

            }, cb_d);

            cb_d(start, end);

        });
    </script>
