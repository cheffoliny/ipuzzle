<?php
    echo '
    <div class="row">
        <div class="col-md-12 col-sm-12">&nbsp;</div>

    </div>
    <div class="row">
        <div class="col-md-4 col-sm-6">&nbsp;</div>

        <div class="form-box col-md-4 col-sm-6" id="login-box">
            <div class="btn btn-lg btn-flat btn-block header bg-blue"><i>i</i> Monitor</div>

            <form action="" method="post">

                <div class="body btn btn-xs btn-block bg-black ">
                    <br />';

                        if( isset($_SESSION['msg']['login-err']) ) {
                            echo '<div class="text-right" style="color: #ff5b57;"><br />'.$_SESSION['msg']['login-err'].'<br /></div>';
                            unset($_SESSION['msg']['login-err']);
                        } else {
                            echo '<div class="text-right">Въведете данни за потребител</div>';
                        }

    echo '          <br />

                    <div class="form-group">
                        <input type="text" name="username" id="username" class="form-control" placeholder="Потребител..."/>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" id="password"  class="form-control" placeholder="Парола..."/>
                    </div>
                    <div class="form-group btn float-left">
                        <input type="checkbox"  name="rememberMe" id="rememberMe" value="1" checked="checked" /> &nbsp; Запомни ме
                    </div>
                </div>
                <div class="footer bg-black">
                    <button type="submit"  name="submit" value="Login" class="btn bg-blue btn-block">Вход</button>
                    <p><a href="#" class="text-center">iMonitor</a></p>
                </div>
            </form>

        </div>

        <div class="col-md-4 col-sm-6">&nbsp;</div>
    </div>';
?>