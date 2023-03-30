<?php

$avatar_path = "../intelisod/person_images/";

if(!defined('INCLUDE_CHECK')) die('Операцията не е позволена');

if( isset($_POST['submit']) && $_POST['submit']=='Login' )
{
    // Checking whether the Login form has been submitted

    $err = array();
    // Will hold our errors


    if(!$_POST['username'] || !$_POST['password'])
        $err[] = 'Всички полета трябва да бъдат попълнение!';

    if(!count($err))
    {
        $_POST['username'] = mysqli_real_escape_string( $db_system, $_POST['username']);
        $_POST['password'] = mysqli_real_escape_string( $db_system, $_POST['password']);
        $_POST['rememberMe'] = (int)$_POST['rememberMe'];

        // Escaping all input data

        $uQuery	=	"
				SELECT p.id AS id, sa.id AS sID, sa.username AS usr, p.fname AS first_name, p.lname AS last_name, sa.has_debug AS admin
				FROM intelli_system.access_account sa
				LEFT JOIN personnel.personnel p ON p.id = sa.id_person
				WHERE sa.to_arc = 0 AND p.status = 'active' AND sa.username='{$_POST['username']}' AND sa.password='".md5($_POST['password'])."' ";

        $uResult= mysqli_query( $db_system, $uQuery	);

        $row = mysqli_fetch_assoc( $uResult );
        //echo ($row['usr']);
        if($row['usr'])
        {
            // If everything is OK login

            $_SESSION['usr'         ] = $row['usr'];
            $_SESSION['uid'         ] = $row['id'];  // Служител ID
            $_SESSION['id'          ] = $row['sID']; // Потребителско ID
            if( file_exists( $avatar_path.$row['uid'].".jpg" ) ) {
                $_SESSION['avatar'      ] = "<img class='avatar' alt='' src='".$avatar_path.$row['uid'].".jpg' ></img>";
            } else {
                $_SESSION['avatar'      ] = "<i class='glyphicon glyphicon-user'></i>";
            }
            $_SESSION['admin'       ] = $row['admin'];
            $_SESSION['first_name'  ] = $row['first_name'];
            $_SESSION['last_name'   ] = $row['last_name' ];
            $_SESSION['rememberMe'  ] = $_POST['rememberMe'];

            // Store some data in the session

            setcookie('loginRemember',$_POST['rememberMe']);

            update_alert_time( $_SESSION['id'] );
        }
        else $err[]='Грешно потребителско име и/или парола!';
    }

    if($err)
        $_SESSION['msg']['login-err'] = implode('<br />',$err);
    // Save the error messages in the session

    header("Location: index.php");
    exit;
}
else if( isset($_POST['submit']) && $_POST['submit']=='Register' )
{
    // If the Register form has been submitted

    $err = array();

    if(strlen($_POST['username'])<4 || strlen($_POST['username'])>32)
    {
        $err[]='Потребителското име трябва да бъде между 3 и 32 символа!';
    }

    if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
    {
        $err[]='Потребителското име съдържа непозволени символи!';
    }

    if(!checkEmail($_POST['email']))
    {
        $err[]='Въведеният е-мейл адрес е невалиден!';
    }

    if(!count($err))
    {
        // If there are no errors

        $pass = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
        // Generate a random password

        $_POST['username'] = mysqli_real_escape_string( $db_system, $_POST['username']);
        $_POST['email'] = mysqli_real_escape_string( $db_system, $_POST['email']);
        // Escape the input data


        mysqli_query("	INSERT INTO members(usr,pass,email,regIP,dt)
						VALUES(
						
							'".$_POST['username']."',
							'".md5($pass)."',
							'".$_POST['email']."',
							'".$_SERVER['REMOTE_ADDR']."',
							NOW()
							
						)");

        if( mysqli_affected_rows( $db_system ) ==1 )
        {
            send_mail(	'powerlink.backup@gmail.com',
                $_POST['email'],
                'Регистрация - Вашата нова парола',
                'Вашата парола е: '.$pass);

            $_SESSION['msg']['reg-success']='Изпратен Ви е е-мейл с новата парола!';
        }
        else $err[]='Това потребителско име вече се използва!';
    }

    if(count($err))
    {
        $_SESSION['msg']['reg-err'] = implode('<br />',$err);
    }

    header("Location: index.php");
    exit;
}

?>