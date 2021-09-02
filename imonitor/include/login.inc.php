<?php
$avatar_path = "../intelisod/person_images/";

if(!defined('INCLUDE_CHECK')) die('Операцията не е позволена');

if( isset($_POST['submit']) && $_POST['submit']=='Login' ) {
    // Checking whether the Login form has been submitted
    ob_start();
    $err = array();
    // Will hold our errors


    if (!$_POST['username'] || !$_POST['password'])
        $err[] = 'Всички полета трябва да бъдат попълнение!';

    if (!count($err)) {
        $_POST['username'] = mysqli_real_escape_string($db_system, trim($_POST['username']) ) ;
        $_POST['password'] = mysqli_real_escape_string($db_system, trim($_POST['password']) );
        $_POST['rememberMe'] = (int)$_POST['rememberMe'];

        // Escaping all input data

        $uQuery = "
				SELECT
                    IF( sa.id_profile = 1, 'admin', GROUP_CONCAT(al.name) ) AS access,
                    p.id                  AS id,
                    sa.username           AS usr,
                    p.fname               AS first_name,
                    p.lname               AS last_name,
                    sa.has_debug          AS admin,
                    DATE_FORMAT( p.date_from, '%d.%m.%Y' ) AS from_date
                FROM intelli_system.access_account sa
                LEFT JOIN personnel.personnel p						ON p.id = sa.id_person
                LEFT JOIN intelli_system.access_profile ap			ON ap.id = sa.id_profile
                LEFT JOIN intelli_system.access_level_profile alp	ON alp.id_profile = ap.id
                LEFT JOIN intelli_system.access_level al	      	ON al.id = alp.id_level AND al.name like 'imonitor%'
                WHERE sa.to_arc = 0 AND p.status = 'active' AND ( p.vacate_date = '0000-00-00' OR p.vacate_date > NOW() ) AND
                      sa.username='{$_POST['username']}' AND sa.password=md5('".$_POST['password']."') ";

        $uResult = mysqli_query($db_system, $uQuery);

        $row = mysqli_fetch_assoc($uResult);
        //echo ($row['usr']);
        if ($row['usr']) {
            // If everything is OK login

            $_SESSION['usr'] = $row['usr'];
            $_SESSION['mid'] = $row['id'];
            if (file_exists($avatar_path . $row['id'] . ".jpg")) {
                $_SESSION['avatar'] = "src='" . $avatar_path . $row['id'] . ".jpg'";
            } else {
                $_SESSION['avatar'] = "src='./imon/user.jpg'";
            }
            $_SESSION['access']     = $row['access'];
            $_SESSION['admin']      = $row['admin'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name']  = $row['last_name'];
            $_SESSION['from_date']  = $row['from_date'];
            //$_SESSION['rememberMe'  ] = $_POST['rememberMe'];

            // Store some data in the session
            if ($_POST['rememberMe'] == 1) {
                setcookie('loginRemember', $_POST['rememberMe'], time() + 3600 * 24 * 30);
            }
            //update_alert_time( $_SESSION['id'] );
        } else $err[] = 'Грешно потребителско име и/или парола!';
    }

    if( count($err) ) {
        $_SESSION['msg']['login-err'] = implode('<br />', $err);
    } // Save the error messages in the session

    header("Location: index.php");
    exit;
}
else if( isset($_POST['submit']) && $_POST['submit']=='Register' )
{
    // If the Register form has been submitted

//    $err = array();
//
//    if(strlen($_POST['username'])<4 || strlen($_POST['username'])>32)
//    {
//        $err[]='Потребителското име трябва да бъде между 3 и 32 символа!';
//    }
//
//    if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
//    {
//        $err[]='Потребителското име съдържа непозволени символи!';
//    }
//
//    if(!checkEmail($_POST['email']))
//    {
//        $err[]='Въведеният е-мейл адрес е невалиден!';
//    }

//    if(!count($err))
//    {
//        // If there are no errors
//
//        $pass = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
//        // Generate a random password
//
//        $_POST['email'] = mysql_real_escape_string($_POST['email']);
//        $_POST['username'] = mysql_real_escape_string($_POST['username']);
//        // Escape the input data
//
//
//        mysql_query("	INSERT INTO members(usr,pass,email,regIP,dt)
//						VALUES(
//
//							'".$_POST['username']."',
//							'".md5($pass)."',
//							'".$_POST['email']."',
//							'".$_SERVER['REMOTE_ADDR']."',
//							NOW()
//
//						)");
//
//        if(mysql_affected_rows($db_system)==1)
//        {
//            send_mail(	'powerlink.backup@gmail.com',
//                $_POST['email'],
//                'Регистрация - Вашата нова парола',
//                'Вашата парола е: '.$pass);
//
//            $_SESSION['msg']['reg-success']='Изпратен Ви е е-мейл с новата парола!';
//        }
//        else $err[]='Това потребителско име вече се използва!';
//    }

//    if(count($err))
//    {
//        $_SESSION['msg']['reg-err'] = implode('<br />',$err);
//    }

    header("Location: index.php");
    exit;
}
?>