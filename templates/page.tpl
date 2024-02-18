<!DOCTYPE html>
<html>
<head>
    <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="images/favicon.ico" >
    <link href="css/index.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="js/bowser.min.js"></script>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">


	<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCJCSAQPKRrx7XlFccO_EkFqzZ74-EcA8o&libraries=places async"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>

	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
	<script type="text/javascript">
	{literal}
		jQuery.noConflict();
    {/literal}
	</script>
    {*<script src="js/dropdown.js"			type="text/javascript"></script>*}
    <script type="text/javascript" language="javascript" src="js/suggest_prototype.js"></script>
    <script type="text/javascript" language="javascript" src="js/suggest.js?version=1"></script>
    <script type="text/javascript" language="javascript" src="js/framework.js"></script>
    <script type="text/javascript" language="javascript" src="js/framework_general.js"></script>
    <script type="text/javascript" language="javascript" src="js/common_dialogs.js?version=2"></script>
    <script type="text/javascript" language="javascript" src="js/autoselect.js"></script>
    <script type="text/javascript" language="javascript" src="js/xmlrpc.js?version=2"></script>
    <script type="text/javascript" language="javascript" src="js/format.js"></script>
    <script type="text/javascript" language="javascript" src="js/misc.js?version=1"></script>

	<link href="css/fa5/css/all.css" rel="stylesheet" type="text/css" />
    {*<link href="css/bs3/iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet" type="text/css">*}
    <link href="css/bootstrap-intelli.css" rel="stylesheet" type="text/css">
    <link href="css/menu.css" rel="stylesheet" type="text/css">
    <title>.: iPuzzle - Order It... :.</title>

    <script type="text/javascript" language="javascript">
        {literal}

        document.onkeydown =
                function(e){
                    if (document.all){
                        keycd = event.keyCode;
                    } else {
                        keycd = e.keyCode;
                    }
                    if (keycd == 27) return false;
                }
        {/literal}
        rpc_action_script = "api/api_{$page}.php";
        rpc_eol_debug={$eol_debug};
        rpc_local_temp_dir = "{$local_temp_dir|escape:javascript}";
        rpc_is_save_file = {$is_save_file};
    </script>

</head>
<body>


<div id="systemMessageBG" style="display:none;"></div>
<div class="canvasModal2" id="systemMessageDialog" style="width:300px;display:none;">
	<div style="width:300px;height:20px;position:relative;background-color:#cacaca;">
		<div style="width:280px;float:left;font-size:14px;">
		Системно съобщение
		</div>
		<div class="closeDialog" onclick="closeSystemMessage();">
			<span   style="float:right;"></span>
		</div>
	</div>
	<div style="height:50px;padding-top:10px;position:relative;">
		<div style="float:left;">
            <i class="fa fa-warning fa-2x text-red"></i>
		</div>
		<div style="float:left;width:230px;padding-top:10px;" id="sytemMessageValue">
			Некоректно въведени данни
		</div>
	</div>
	<div style="text-align: center;">
		<button class="search" onclick="closeSystemMessage();return false;">Добре</button>
	</div>
</div>
	
{if $error}
<div class="container">
	<div class="row">
		<div class="col bg-gradient-danger">
			{if $error eq "missingFile"}Страницата, която искате да отворите не съществува!<br />{/if}
			{if $error eq "missingReject"}Нямате достъп до ресурса!{/if}
		</div>
	</div>
</div>
{else}
{*	{if $play_flex_file}*}
{*	    {assign var=pageresult value="flex_page.tpl"}*}
{*	    {include file=$pageresult}*}
{*	    *}
{*	{else}*}
	    {assign var=pageresult value="$page.tpl"}
	    {include file=$pageresult}
{*	{/if}*}
{/if}

<script type="text/javascript" language="javascript"  src="js/dlcalendar.js"></script>

</body>
</html>