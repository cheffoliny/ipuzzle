<!DOCTYPE html>
<html lang="en">
<head>
    <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>.: iPuzzle - ERP & BPM System :.</title>

    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>

    <link href="css/login.css" 				        type="text/css" rel="stylesheet" />
    <link href="css/index.css" 				        type="text/css" rel="stylesheet" />

    <link href="css/bootstrap-intelli.css"  rel="stylesheet" type="text/css">
</head>

<body class="text-center">

    <form class="form-signin" action="index.php?do=login" name=form method="POST" target="_self">

        <img class="mb-4" src="images/logo.png" alt="" /><br />
        {if $message}{$message}
        {elseif $message eq "incorectAcceptOfiices"}<h5 class="mb-3 font-weight-normal text-danger">Потребителят няма достъп!</h5>
        {else}<h5 class="mb-3">Въведете потребителско име и парола за достъп!</h5>{/if}

        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
                <span class="fas fa-user fa-lg fa-fw" data-fa-transform="right-22 down-6"></span>
            </div>
            <input class="form-control pl-5 mr-3" type="text" value="" id="name" name="username" placeholder="Потребителско име"  required="" autofocus="" />
        </div>

        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
                <span class="fa fa-key fa-lg  fa-fw" data-fa-transform="right-22 down-6"></span>
            </div>
            <input class="form-control pl-5 mr-3" type="password" value="" name="password" placeholder="Парола за достъп..." required="" />
        </div>
        {*<div class="input-group input-group-sm btn-block">*}
            {*<input type="text" value="" id="code" name="code" style="width: 85px; height: 26px; border: 1px solid #fefefe; background: rgba(255,255,255,0.5);" autocomplete="off"/>*}
            {*<img src="include/code.php" style="float: right; border-top: 3px solid #fefefe; border-bottom: 3px solid #fefefe;" />*}
        {*</div>*}
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
            &nbsp;
            </div>
            <button class="btn btn-md btn-primary btn-block ml-4 mr-3" type="submit"><i class="fas fa-sign-in-alt fa-lg text-white"></i> Вход</button>
        </div>
        <p class="mt-5 mb-3 text-muted">Разработено за © ИНФРА ЕООД 2013</p>

	</form>

    <script>document.getElementById('name').focus(); document.getElementById('name').select();</script>
</body>
</html>

