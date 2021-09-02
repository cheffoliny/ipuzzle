<?php

if( $_SESSION['access'] != 'admin' && strpos($_SESSION['access'],'imonitor_main_ifinance') === false ) {
    echo  'Нямате права за достъп'; die();
}

?>
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-12 connectedSortable">
            <!-- Box (with bar chart) -->
            <div class="box box-danger" id="loading-example">
                <div class="box-header">
                    <i class="fa fa-money"></i>
                    <h3 class="box-title">ЗАКЪСНЕЛИ ПЛАЩАНИЯ</h3>
                    <!-- tools box -->
                    <div class="pull-right box-tools">
                        <button class="btn btn-default btn-sm refresh-btn" data-toggle="tooltip" title="Reload"><i class="fa fa-refresh"></i></button>
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
                    </div><!-- /. tools -->
                </div><!-- /.box-header -->
                <div class="box-footer no-border">
                    <div class="row">
                        <?php include_once('ajax/ajax_finance_delayed_payments.php'); ?>
                    </div><!-- /.row - inside box -->
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </section><!-- left col -->
    </div><!-- /.row (main row) -->

    <div class="row">
        <!-- Left col -->
        <section class="col-lg-6 connectedSortable">
            <div class="box box-danger">
                <div class="box-header">
                    <i class="fa fa-home"></i>
                    <h3 class="box-title">НАЛИЧНОСТ <-> ПРЕДПЛАТИЛИ </h3>
                    <div class="box-tools pull-right">
                        <div class="pull-right box-tools">
                            <button class="btn btn-default btn-sm refresh-btn" data-toggle="tooltip" title="Обнови" data-original-title="Reload" onclick="no_client_no_taxes(); return false;"><i class="fa fa-refresh"></i></button>
                            <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <p><span id="compositebar" data-composite-line-color="#a3e2fe">
                            <?php
                                list( $strReturn, $strBank, $strTotal, $strMonth, $maxValue, $bankLineColor ) = get_Prepaid_Objects();
                                echo $strReturn;
                            ?>
                    </span></p>
                    <p class="text-left"><?php  echo $strMonth; ?></p>
                </div><!-- /.box-body -->
            </div><!-- /.box -->

        </section><!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-6 connectedSortable">
            <!-- Map box -->
            <div class="box box-warning">
                <div class="box-header">
                    <i class="fa fa-power-off"></i>
                    <h3 class="box-title"> ПРЕДПЛАТИЛИ </h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                        <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <div class="todo-list" id="prepaid_taxes"></div>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </section><!-- right col -->
    </div><!-- /.row (main row) -->

    <div class="row">
        <!-- Left col -->
        <section class="col-lg-6 connectedSortable">
            <div class="box box-danger">
                <div class="box-header">
                    <i class="fa fa-home"></i>
                    <h3 class="box-title">ОБЕКТИ КЛИЕНТ / ТАКСА / ТЕХНИКА / СТАРТ</h3>
                    <div class="box-tools pull-right">
                        <div class="pull-right box-tools">
                            <button class="btn btn-default btn-sm refresh-btn" data-toggle="tooltip" title="Обнови" data-original-title="Reload" onclick="no_client_no_taxes(); return false;"><i class="fa fa-refresh"></i></button>
                            <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Скрий"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-danger btn-sm" data-widget='remove' data-toggle="tooltip" title="Премахни"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <ul class="todo-list" id="no_client_no_taxes"></ul>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </section><!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->

    </div><!-- /.row (main row) -->

    <script type="text/javascript">
        $(function () {

            $(".sparkline").each(function () {
                var $this = $(this);
                $this.sparkline('html', $this.data());
            });
            drawDocSparklines();

        });
        function drawDocSparklines() {

            var arrayBank   = [<?php echo $strBank; ?>];
            arrayBank.toString();
            var arrayTotal  = [<?php echo $strTotal; ?>];
            arrayTotal.toString();
            var maxValue    =  <?php echo $maxValue;   ?>;

            var bankLineColor=  "<?php echo $bankLineColor;   ?>";

            // Bar + line composite charts
            $('#compositebar').sparkline('html', {
                type: 'bar', barWidth: 40, barSpacing: 5, width: 400, height: 240, barColor: '#99f', tooltipChartTitle: 'Предплатили', tooltipPrefix: 'За месеца: ',
                enableTagOptions: true,
                chartRangeMax: maxValue,
                drawNormalOnTop: true});
            $('#compositebar').sparkline(arrayBank,
                {composite: true, fillColor: false, lineColor: bankLineColor, spotRadius: 5, highlightLineColor: false, chartRangeMax: maxValue,  enableTagOptions: false, tooltipPrefix: 'Наличност: '});
            $('#compositebar').sparkline(arrayTotal,
                {composite: true, fillColor: false, lineColor: 'blue', spotRadius: 5, highlightLineColor: '#efefef', chartRangeMax: maxValue, enableTagOptions: false, tooltipPrefix: 'Общо предплатили: '});
1
        }

    </script>