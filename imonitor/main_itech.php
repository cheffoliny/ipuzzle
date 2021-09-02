<?php

if( $_SESSION['access'] != 'admin' && strpos($_SESSION['access'],'imonitor_main_itech') === false ) {
    echo  'Нямате права за достъп'; die();
}

?>
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-6 connectedSortable">
        <!-- NO TEST List -->
        <div class="box box-danger">
            <div class="box-header">
                <i class="fa fa-chain-broken"></i>
                <h3 class="box-title">ОБЕКТИ БЕЗ ТЕСТ</h3>

                ( <input type="text"   id="test_type" name="test_type" value="0" disabled="disabled" class="no-border" style="text-align: center; width: 23px;" /> )

                <div class="box-tools pull-right">
                    <div class="pull-right box-tools">
                        <div class="btn-group">
                            <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-filter"></i></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a id="350" onclick="changeNoTest(350); no_test(); return false;"><i class="fa fa-eye-slash"></i>Отпаднала връзка</a></li>
                                <li><a id="602" onclick="changeNoTest(602); no_test(); return false;"><i class="fa fa-text-width text-warning"></i> Няма тест ( ПАНЕЛ )</a></li>
                                <li><a id="611" onclick="changeNoTest(611); no_test(); return false;"><i class="fa fa-text-width text-danger"></i> Няма тест ( GPRS )</a></li>
                                <li class="divider"></li>
                                <li><a id="0"   onclick="changeNoTest(0);   no_test(); return false;"><i class="fa fa-refresh"></i> Всички </a></li>
                            </ul>
                        </div>

                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul class="todo-list" id="no_test"></ul>
            </div><!-- /.box-body -->
        </div><!-- /.box -->

        <!-- NO TEST List -->
        <div class="box box-primary">
            <div class="box-header">
                <i class="fa fa-signal"></i>
                <h3 class="box-title"> НИСКО НИВО НА СИГНАЛА</h3>
                <div class="box-tools pull-right">
                    <div class="pull-right box-tools">
                        <button class="btn btn-default btn-sm refresh-btn" data-toggle="tooltip" title="Обнови" data-original-title="Reload" onclick="low_level(); return false;"><i class="fa fa-refresh"></i></button>
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul class="todo-list" id="low_level"></ul>
            </div><!-- /.box-body -->
        </div>

        <div class="box box-info">
            <div class="box-header">
                <i class="fa fa-signal"></i>
                <h3 class="box-title"> ЗАЦИКЛИЛИ ИЛИ ОБЕКТИ С МНОГО СИГНАЛИ</h3>
                <div class="box-tools pull-right">
                    <div class="pull-right box-tools">
                        <button class="btn btn-default btn-sm refresh-btn" data-toggle="tooltip" title="Обнови" data-original-title="Reload" onclick="stuck_in_objects(); return false;"><i class="fa fa-refresh"></i></button>
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul class="todo-list" id="stuck_in_objects"></ul>
            </div><!-- /.box-body -->
        </div>

    </section><!-- /.Left col -->
    <!-- right col (We are only adding the ID to make the widgets sortable)-->
    <section class="col-lg-6 connectedSortable">
        <!-- Map box -->
        <div class="box box-warning">
            <div class="box-header">
                <i class="fa fa-power-off"></i>
                <h3 class="box-title"> НИСКИ БАТЕРИИ / ~220V </h3>
                ( <input type="text"   id="ac_dc" name="ac_dc" value="0" disabled="disabled" style="text-align: center; border: 0px; background: inherit; width: 23px;" /> )

                <div class="box-tools pull-right">
                    <div class="btn-group">
                        <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-filter"></i></button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a id="302" onclick="changeACDC(302); no_220(); return false;"><i class="fa fa-battery-0"></i> Само с ниски батерии</a></li>
                            <li><a id="301" onclick="changeACDC(301); no_220(); return false;"><i class="fa fa-flash"></i> Само без 220V</a></li>
                            <li class="divider"></li>
                            <li><a id="0" onclick="changeACDC(0);   no_220(); return false;"><i class="fa fa-refresh"></i> Всички </a></li>
                        </ul>
                    </div>

                    <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                    <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul class="todo-list" id="no_220"></ul>
            </div><!-- /.box-body -->
        </div>
        <!-- /.box -->

        <!-- Map box -->
        <div class="box box-success">
            <div class="box-header">
                <i class="fa fa-search"></i>
                <h3 class="box-title"> СЪС СИГНАЛ </h3>
                <div class="box-tools pull-right">
                    <div class="pull-right box-tools" id="additional_stuff">
                        <span>
                            <a class="btn btn-sm btn-success" data-toggle="modal" data-target="#compose-modal"><i class="fa fa-check"></i> Сигнали</a>
                        </span>
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul class="todo-list" id="is_signal"></ul>
            </div><!-- /.box-body -->
        </div>
        <!-- /.box -->

        <!-- Map box -->
        <div class="box box-success">
            <div class="box-header">
                <i class="fa fa-search"></i>
                <h3 class="box-title"> ПОВТОРЕНИЯ НА СИГНАЛ </h3>
                <div class="box-tools pull-right">
                    <div class="pull-right box-tools" id="additional_stuff">
                        <span>
                            <a class="btn btn-sm btn-success" data-toggle="modal" data-target="#repeat-modal"><i class="fa fa-check"></i> Сигнали</a>
                        </span>
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul class="todo-list" id="repeat_signal"></ul>
            </div><!-- /.box-body -->
        </div>
        <!-- /.box -->
    </section><!-- right col -->
</div><!-- /.row (main row) -->

<div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-check"></i> Избери сигнал</h4>
            </div>

            <div class="modal-body">
                <form id="form1" name="form1" onsubmit="$(e).preventDefault(); return false" method="post">
                <?php get_signals(0,0); ?>

                <button type="button" class="btn btn-success btn-sm" data-dismiss="modal" onclick="selectSignals(0);"> <i class="fa fa-search"></i> Търси &nbsp; &nbsp; &nbsp;</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Затвори </button>
               </form>
            </div>


        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="repeat-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-check"></i> Избери сигнал</h4>
            </div>

            <div class="modal-body">
                <form id="form2" name="form2" onsubmit="$(e).preventDefault(); return false" method="post">
                    <?php get_signals(1,0); ?>

                    <button type="button" class="btn btn-success btn-sm" data-dismiss="modal" onclick="selectSignals(1);"> <i class="fa fa-search"></i> Търси &nbsp; &nbsp; &nbsp;</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Затвори </button>
                </form>
            </div>


        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
    function changeNoTest(value) {
        document.getElementById('test_type').value = value;
    }

    function changeACDC(value) {
        document.getElementById('ac_dc').value = value;
    }
</script>
