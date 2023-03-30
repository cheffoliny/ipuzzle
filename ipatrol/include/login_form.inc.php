<?php
?>

    <div class="container-fluid">
        <div id="page-login" class="row">
            <div class="col-xs-12 col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                <div class="text-right">

                </div>
                <div class="box">
                    <div class="box-content">
<!--                        <button class="btn btn-sm btn-danger"   id="play_start" onclick="IntelliSOD.playSound('beep',true)" > Start    </button>-->
<!--                        <button class="btn btn-sm btn-danger"   id="play_stop"  onclick="IntelliSOD.stopSound()"            > Stop     </button>-->
                        <form action="" method="post">
                            <div class="text-right"><img src="./images/logo.png" style="border: 0px;"></div>
                            <!-- Login Form -->
                        <?php

                            if( isset($_SESSION['msg']['login-err']) ) {
                                echo '<div class="text-right" style="color: #ff5b57;"><br />'.$_SESSION['msg']['login-err'].'<br /></div>';
                                unset($_SESSION['msg']['login-err']);
                            } else {
                                echo '<div class="text-right"><br />Въведете данни за потребител<br /></div>';
                            }
                        ?>

                            <div class="form-group">
                                <label class="control-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username"       />
                            </div>
                            <div class="form-group">
                                <label class="control-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password"   />
                            </div>
                            <div class="form-group">
                                <label>
                                    <input name="rememberMe" id="rememberMe" type="checkbox" checked="checked" value="1" style="margin: 0px;" />
                                    &nbsp;Запомни ме
                                </label>
                                <input type="submit" name="submit" value="Login" class="btn btn-sm btn-primary pull-right" />
                            </div>
                        </form>

                        <div class="text-center">
                            <br />2013 &copy; Инфра СОТ
                        </div>
                    </div>
                </div>

                <div class="text-left" style="font-size: 0.7em;">

                </div>
            </div>
        </div>
    </div>
<!-- Register Form -->

<?php
?>