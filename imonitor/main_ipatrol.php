<?php

if( $_SESSION['access'] != 'admin' && strpos($_SESSION['access'],'imonitor_main_ipatrol') === false ) {
    echo  'Нямате права за достъп'; die();
}

?>

<!--<a class="btn btn-block btn-default" data-toggle="modal" data-target="#compose-modal" style="padding-top: 10px; padding-bottom: 10px;"><i class="fa fa-pencil"></i> Заявка </a>-->
<input type="hidden"   id="type_signal" name="type_signal" value="0"  />
<input type="hidden"   id="id_receiver" name="id_receiver" value="0"  />
<!-- Начало на архива -->
        <div class="row">
            <div class="col-xs-12 connectedSortable">
                <div class="box box-primary">
                    <div class="box-header">
                        <i class="fa fa-signal"></i>
                        <h3 class="box-title"> Мониторинг &nbsp; &nbsp; &nbsp;</h3>

                        <div class="box-tools" style="width: 400px;">
                            <div class="input-group input-group-sm btn-flat">
                                <div class="input-group-btn">
                                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Применик <span class="fa fa-caret-down"></span></button>
                                  <ul class="dropdown-menu">
                                        <li class="divider"></li>
                                        <li><a href="#" onclick="changeReceiver(0); return false;">-- Всички --</a></li>
                                        <li class="divider"></li>
                                        <?php echo get_receivers(); ?>
                                  </ul>
                                </div>
                                <span class="input-group-btn" title="ТЕСТ ( GPRS )">
                                    <button type="button" class="btn btn-danger btn-flat">
                                        <input type="checkbox" value="11" name="test_id_11" id="test_id_11" checked> <i class="fa fa-text-width"></i>
                                    </button>
                                </span>
                                <span class="input-group-btn" title="ТЕСТ ( GPRS )">
                                    <button type="button" class="btn btn-warning btn-flat">
                                        <input type="checkbox" value="14" name="test_id_14" id="test_id_14" checked> <i class="fa fa-text-width"></i>
                                    </button>
                                </span>
                                <input class="form-control" type="text"     id="num"         name="num"         placeholder="№ Обект"   />
                                <span class="input-group-btn" data-toggle="btn-toggle">
                                    <button class="btn btn-info"    id="all"      onclick="changeSignalType(0); return false;"><i class="fa fa-check"></i></button>
                                    <button class="btn btn-warning" id="warning"  onclick="changeSignalType(1); return false;"><i class="fa fa-warning"></i></button>
                                    <button class="btn btn-danger"  id="alarms"   onclick="changeSignalType(2); return false;"><i class="fa fa-bell"></i></button>
                                </span>
                            </div>
                        </div>

                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-condensed table-striped" id="monitoring"></table>
                    </div><!-- /.box-body -->
                </div>
            </div><!-- /.col -->
        </div>
    </section><!-- /.content -->


    <!-- add new calendar event modal -->
    <div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Compose New Message</h4>
                </div>
                <form action="#" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">TO:</span>
                                <input name="email_to" type="email" class="form-control" placeholder="Email TO">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">CC:</span>
                                <input name="email_to" type="email" class="form-control" placeholder="Email CC">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">BCC:</span>
                                <input name="email_to" type="email" class="form-control" placeholder="Email BCC">
                            </div>
                        </div>
                        <div class="form-group">
                            <textarea name="message" id="email_message" class="form-control" placeholder="Message" style="height: 120px;"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="btn btn-success btn-file">
                                <i class="fa fa-paperclip"></i> Attachment
                                <input type="file" name="attachment">
                            </div>
                            <p class="help-block">Max. 32MB</p>
                        </div>

                    </div>
                    <div class="modal-footer clearfix">

                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Discard</button>

                        <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-envelope"></i> Send Message</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <script>
        function changeSignalType(value) {
            document.getElementById('type_signal').value = value;
//            if( value == 2 ) {
//                document.getElementById('alarms').className = 'active';
//                document.getElementById('warning').className = 'inactive';
//                document.getElementById('all').className = 'inactive';
//            } else if ( value == 1 ) {
//                document.getElementById('warning').className = 'active';
//                document.getElementById('alarms').className = 'inactive';
//                document.getElementById('all').className = 'inactive';
//            } else {
//                document.getElementById('all').className = 'active';
//                document.getElementById('warning').className = 'inactive';
//                document.getElementById('alarms').className = 'inactive';
//            }
        }

        function changeReceiver(value) {
            document.getElementById('id_receiver').value = value;
            document.getElementById('id_receiver').value = value;
        }
    </script>