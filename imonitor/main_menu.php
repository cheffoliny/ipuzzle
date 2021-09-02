<div class="wrapper row-offcanvas row-offcanvas-left">
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="left-side sidebar-offcanvas">
        <section class="sidebar">
            <ul class="sidebar-menu">
                <?php
	
                    if( $_SESSION['access'] == 'admin' || strpos($_SESSION['access'],'imonitor_main_itech') !== false ) {
                        ?>
                        <li>
                            <a href="index.php?action=itech">
                                <i class="fa fa-gears"></i> <span>iTech</span>
                            </a>
                        </li>
                        <?php
                    }

                    if( $_SESSION['access'] == 'admin' || strpos($_SESSION['access'],'imonitor_main_ipatrol') !== false ) {
                        ?>
                        <li>
                            <a href="index.php?action=ipatrol">
                                <i class="fa fa-eye"></i> <span>iPatrol</span>
                            </a>
                        </li>
                        <?php
                    }

                    if( $_SESSION['access'] == 'admin' || strpos($_SESSION['access'],'imonitor_main_ireport') !== false ) {
                        ?>
                        <li>
                            <a href="index.php?action=ireport">
                                <i class="fa fa-line-chart"></i> <span>iReport</span>
                            </a>
                        </li>
                        <?php
                    }

                    if( $_SESSION['access'] == 'admin' || strpos($_SESSION['access'],'imonitor_main_ifinance') !== false ) {

                        ?>
                        <li>
                            <a href="index.php?action=ifinance">
                                <i class="fa fa-euro"></i> <span>iFinance</span><small class="badge pull-right bg-red">new</small>
                            </a>
                        </li>
                        <?php
                    }

                    ?>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <!-- Main content -->
        <section class="content" id="mainContent">