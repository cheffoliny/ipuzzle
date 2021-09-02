<?php

define('INCLUDE_CHECK',true);

require_once( "../config/session.inc.php"	);
require_once( "../config/connect.inc.php"	);
require_once( "../config/dictionar.inc.php" );

if( isset($_SESSION['mid']) ):

    ob_start();

    function handle_drop($errno, $errstr, $errfile, $errline){
        if( $errno == E_WARNING ){
            echo 'Проблем с връзката! Проверете всички връзки и опитайте да обновите страницата!';
            $ob = ob_get_clean();
            header("HTTP/1.0 500 Internal server error");
            echo $ob;

        }
    }

    set_error_handler('handle_drop');

    $BR = 20; /* Брой повторения на сигнали - за да се счита обекта като зациклил  */
    $strMonth   = date('Ym');

    $rQuery     = "
                SELECT
                    COUNT(a.id)     AS 'BR'   ,
                    o.id            AS 'oId'  ,
                    o.num           AS 'oNum' ,
                    o.name          AS 'oName',
                    MAX(a.msg_time) AS  'mTime'
                FROM archiv_".$strMonth." a
                LEFT JOIN messages m ON m.id = a.id_msg AND m.to_arc = 0
                LEFT JOIN objects  o ON o.id = m.id_obj AND o.id_status != 4
                WHERE
                     a.msg_time > DATE_ADD( NOW(), INTERVAL -1 HOUR )
                GROUP BY o.id
                HAVING BR >
                ORDER BY BR DESC";

    $rResult	=	mysqli_query( $db_sod, $rQuery	) or die( "Error: ".$rQuery );
    $rRows	    =	mysqli_num_rows( $tResult		);

    if( !$rRows ) {
        echo "<a href='#' class='dropdown-toggle' data-toggle='dropdown'>
                <i class='fa fa-tasks'></i>
                <span class='label label-danger'>0</span>
              </a>
              <ul class='dropdown-menu'>
                <li class='header'>You have 9 tasks</li>
                <li></li>
                <li class='footer'>
                    <a href='#'>View all tasks</a>
                </li>
              </ul>";
    } else {

        echo "<a href='#' class='dropdown-toggle' data-toggle='dropdown'>
                <i class='fa fa-tasks'></i>
                <span class='label label-danger'>".$rRows."</span>
              </a>
              <ul class='dropdown-menu'>
                <li class='header'>You have 9 tasks</li>
                <li>";

        while( $rRow = mysqli_fetch_assoc( $rResult ) ) {

            $oID    = isset( $rRow['оId'    ] ) ? $rRow['оId'	 ] : 0;
            $mTime  = isset( $rRow['mTime'  ] ) ? $rRow['mTime'] : 0;
            $tDiff  = isset( $rRow['oNum'   ] ) ? $rRow['oNum'  ] : 0;
            $tDiff  = isset( $rRow['oName'  ] ) ? $rRow['oName'  ] : 0;
            $cBR    = isset( $rRow['BR'     ] ) ? $rRow['BR' ] : 0;

            if( $tSec > 7200 && $tSec < 36000 ){
                $strColor   = "label-warning";
            } else {
                $strColor   = "label-danger";
            }

            echo "<li>
                    <span class='handle' style='color: #cc0000;'><i class='fa fa-eye-slash'></i> &nbsp; ". $mTime ."</span>
                    &nbsp; ". $oName ."
                    <span class='handle' style='float: right;'>
                        <small class='label $strColor'><i class='fa fa-clock-o'></i> ". $tDiff ." </small>
                    </span>
                </li>";
        }
    }

endif;

?>


        <!-- inner menu: contains the actual data -->
        <ul class="menu">
            <li><!-- Task item -->
                <a href="#">
                    <h3>
                        Design some buttons
                        <small class="pull-right">20%</small>
                    </h3>
                    <div class="progress xs">
                        <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">20% Complete</span>
                        </div>
                    </div>
                </a>
            </li><!-- end task item -->
            <li><!-- Task item -->
                <a href="#">
                    <h3>
                        Create a nice theme
                        <small class="pull-right">40%</small>
                    </h3>
                    <div class="progress xs">
                        <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">40% Complete</span>
                        </div>
                    </div>
                </a>
            </li><!-- end task item -->
            <li><!-- Task item -->
                <a href="#">
                    <h3>
                        Some task I need to do
                        <small class="pull-right">60%</small>
                    </h3>
                    <div class="progress xs">
                        <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">60% Complete</span>
                        </div>
                    </div>
                </a>
            </li><!-- end task item -->
            <li><!-- Task item -->
                <a href="#">
                    <h3>
                        Make beautiful transitions
                        <small class="pull-right">80%</small>
                    </h3>
                    <div class="progress xs">
                        <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">80% Complete</span>
                        </div>
                    </div>
                </a>
            </li><!-- end task item -->
        </ul>
    </li>
    <li class="footer">
        <a href="#">View all tasks</a>
    </li>
</ul>