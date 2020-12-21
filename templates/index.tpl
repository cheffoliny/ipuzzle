<!DOCTYPE html>
<html>
<head>
    <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="images/favicon.ico" >
    <link href="css/buttons.css" rel="stylesheet" type="text/css" />
    {*<link href="css/cal.css" rel="stylesheet" type="text/css" />*}

    <link href="css/index.css" rel="stylesheet" type="text/css" />

    <script src="js/framework_general.js"	type="text/javascript"></script>
    <script src="js/autoselect.js"			type="text/javascript"></script>
    <script src="js/framework.js"			type="text/javascript"></script>
    {*<script src="js/performance.js"			type="text/javascript"></script>*}
    {*<script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyAgMeepfn7LP4uw75BUQ8Q79tfuBs4ouKw"></script>*}
    <script type="text/javascript" language="javascript" src="js/bowser.min.js"></script>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <script>
        {literal}
        jQuery.noConflict();
        {/literal}
    </script>


    <link href="css/fa5/css/all.css" rel="stylesheet" type="text/css" />
    <link href="css/bootstrap-intelli.css"  rel="stylesheet" type="text/css">
    <link href="css/menu.css"                   rel="stylesheet" type="text/css">

    <title>.: iPuzzle - ERP & BPM System :.</title>


	<script>
        {literal}
        function do_menu(url){
            if(url!='') document.getElementById('content').src='page.php?page='+url;
        }
        {/literal}
		  
		{*Performance.INTERVAL = {if $system.monitoring_interval}{$system.monitoring_interval}{else}0{/if};*}
		{*Performance.ENABLED  = {if $system.monitoring_enabled}true{else}false{/if};*}
		
	</script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-faded z-1" id="main-menu">
        <a class="navbar-brand" href="#"><label class="label-danger"><i class="fa fa-puzzle-piece"></i></label></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon navbar-toggler-left"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mr-auto mb-1">
                {foreach from=$menu_main item=item}
                <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="javascript:do_menu('{$item.filename}')" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {$item.title} </a>
                {if $item.sublink}
                    <ul class="dropdown-menu dropdown-item-menu" aria-labelledby="navbarDropdownMenuLink">
                        {foreach from=$item.sublink item=item}
                            {if $item.sublink}
                            <li class="dropdown-submenu"><a class="dropdown-item dropdown-item-menu dropdown-toggle" data-toggle="dropdown" href="javascript:do_menu('{$item.filename}')"> {$item.title}</a>
                                    <ul class="dropdown-menu">
                                        {foreach from=$item.sublink item=item}
                                        <li> <a class="dropdown-item dropdown-item-menu" href="javascript:do_menu('{$item.filename}')"> {$item.title}</a> </li>
                                        {/foreach}
                                    </ul>
                            </li>
                            {else}
                                <li><a class="dropdown-item dropdown-item-menu" href="javascript:do_menu('{$item.filename}')"> {$item.title}</a></li>
                            {/if}
                        {/foreach}
                    </ul>
                {else}
                    <li><a class="dropdown-item" href="javascript:do_menu('{$item.filename}')"> {$item.title}</a></li>
                {/if}
                </li>
                {/foreach}
            </ul>

            <ul class="navbar-nav pull-right">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" >
                    <i class="far fa-user fa-lg"></i> &nbsp; {$userdata.name|escape:"html"|truncate:45} [{$userdata.username|escape:"html"}]
                    <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="nav-item dropdown"><a class="nav-link dropdown-item dropdown-item-menu" href="#" onClick="dialog_win('set_curent_user_password',400,200,1,'set_curent_user_password');" title="Парола"> <i class="fa fa-key fa-lg"></i> &nbsp; Смяна на парола</a></li>
                        <li class="divider"></li>
                        <li class="nav-item dropdown"><a class="nav-link dropdown-item dropdown-item-menu" href="#" onClick="document.location.href='logout.php'" title="Изход"> <i class="fas fa-sign-out-alt fa-lg"></i> &nbsp; Изход </a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </nav>
    <iframe id="content" frameborder="0" src="page.php?page=blank_page"></iframe>

	</body>
</html>
