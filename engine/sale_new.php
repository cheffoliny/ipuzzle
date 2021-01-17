<?php
    if (! file_exists('./mix-manifest.json')) {
        die('mix-manifest error!');
    }
    $manifest = json_decode(file_get_contents('./mix-manifest.json'), true);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.ico' />
    <link href="css/fa5/css/all.min.css" rel="stylesheet">
    <link href="<?php echo $manifest['/css/finance/app.css']; ?>" rel="stylesheet">
    <title>Продажба приход</title>
</head>
<body translate="no">
<div id="app">
    <sale-doc/>
</div>
</body>
<script src="js/framework_general.js"></script>
<script src="js/common_dialogs.js"></script>
<script src="<?php echo $manifest['/js/finance/app.js']; ?>"></script>
</html>