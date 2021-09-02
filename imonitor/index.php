<?php

    define("INCLUDE_CHECK",true);
    require_once ( "includes.php"               );
    require_once ( "header.php"                 );

if( !isset($_SESSION['mid']) ):

    require_once ( "./include/login.inc.php"    );

else:

    // Those two files can be included only if INCLUDE_CHECK is defined

    //require_once './include/functions.php';
    require_once ( "main_header.php"            );
    require_once ( "main_menu.php"              );


    switch ( $action ){

        case 'itech':
            if( file_exists('main_itech.php'))
                include('main_itech.php');
            else
                echo ERROR;
            break;

        case 'ipatrol':
            if( file_exists('main_ipatrol.php'))
                include('main_ipatrol.php');
            else
                echo ERROR;
            break;

        case 'ireport':
            if( file_exists('main_ireport.php'))
                include('main_ireport.php');
            else
                echo ERROR;
            break;

        case 'ifinance':
            if( file_exists('main_ifinance.php'))
                include('main_ifinance.php');
            else
                echo ERROR;
            break;

        default:
            if( file_exists('main_itech.php'))
                include('main_itech.php');
            else
                echo ERROR ;

    }
    //require_once ( "action.php"	                );

endif;

    require_once ( "footer.php"	                );

?>