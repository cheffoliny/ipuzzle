<header class="main-header">
    <!-- Logo -->
    <a href="index2.html" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>i</b>Mon</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>i</b>Monitor</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Меню</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <?php

                if( $_SESSION['access'] == 'admin' ) {

                   echo '<li class="dropdown messages-menu" id="stop_alarms"></li>';
                }
                
                ?>


                <li class="dropdown messages-menu" id="no_closed_objects"></li>

                <li class="dropdown messages-menu" id="in_temp_bypass"></li>

                <li class="dropdown messages-menu" id="in_service_mod"></li>

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img <?php echo $_SESSION['avatar']; ?> class="user-image" alt="User Image" />

                        <span class="hidden-xs"><?php echo $_SESSION['usr'] ? "".$_SESSION['first_name'] . $_SESSION['last_name']."" : "" ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img <?php echo $_SESSION['avatar']; ?> class="img-circle" alt="User Image" />
                            <p>
                                <?php echo $_SESSION['usr'] ? "[ ".$_SESSION['usr']." ] ".$_SESSION['first_name'] . $_SESSION['last_name']."" : "" ?>

                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="col-xs-12 text-center">
                                <small>Служител от: <?php echo $_SESSION['from_date']; ?></small>
                            </div>
<!--                            <div class="col-xs-4 text-center">-->
<!--                                <a href="#">Sales</a>-->
<!--                            </div>-->
<!--                            <div class="col-xs-4 text-center">-->
<!--                                <a href="#">Friends</a>-->
<!--                            </div>-->
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat"><i class="fa fa-user"></i>  &nbsp; Профил &nbsp; </a>
                            </div>
                            <div class="pull-right">
                                <a href="?logoff" class="btn btn-default btn-flat"><i class="fa fa-power-off"></i> &nbsp; Изход </a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>



<div class="modal fade bs-example-modal-sm" tabindex="-1" id="myModal" style="display: none;" data-backdrop="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header  alert-danger">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h5 class="modal-title" id="mySmallModalLabel">Прекратяване на всички аларми за сигнал...? </h5>
            </div>
            <div class="modal-body">
                <input id="sName" readonly="" type="text" class="no-border">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="sID" onclick="stop_alarms( this.value ); return false;">Да</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Не</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $("#myModal").on("show.bs.modal", function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var sID = button.data("code"); // Extract info from data-* attributes
            var sName = button.data("signal");

            var modal = $(this);
            modal.find('#sID').val(sID);
            modal.find('#sName').val(sName);
        });
    });
</script>