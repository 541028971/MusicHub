<!--Group Lalaland
Author: Tyron
-->
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland |Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="keywords" content="Photo-Hub Responsive web template, Bootstrap Web Templates, Flat Web Templates, Andriod Compatible web template,
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design"/>
    <script type="application/x-javascript"> addEventListener("load", function () {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() 
        {
            window.scrollTo(0, 1);
        } </script>
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css'/>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- Custom Theme files -->
    <link href="css/style.css" rel='stylesheet' type='text/css'/>
    <!-- Custom Theme files -->
    <!--webfont-->
    <link href='http://fonts.googleapis.com/css?family=Quicksand:300,400,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
    <script src="js/menu_jquery.js"></script>
</head>
<body>
<div class="header">
    <div class="container">
        <div class="logo">
            <h1><a href="index.php">Music Hub</a></h1>
        </div>
        <div class="top_right">
            <?php

                $edit = $_GET['edit'];
                if($edit == 0)
                {
                    include "session.php";
                    echo "<ul>";
                    echo "<li><a href='register.php?edit=0'>Register</a></li>";
                    echo "|";
                    echo "<li class='login'>";
                    echo "<div id='loginContainer'><a href='#' id='loginButton'><span>Login</span></a>";
                    echo "<div id='loginBox'>";
                    echo "<form id='loginForm' action='checkpassword.php' method='POST'>";
                    echo "<fieldset id='body'>";
                    echo "<fieldset>";
                    echo "<label for='username'>Username</label>";
                    echo "<input type='text' name='uname' id='uname'>";
                    echo "</fieldset>";
                    echo "<fieldset>";
                    echo "<label for='password'>Password</label>";
                    echo "<input type='password' name='password' id='password'>";
                    echo "</fieldset>";
                    echo "<input type='submit' id='login' value='Sign in'>";
                    echo "<label for='checkbox'><input type='checkbox' id='checkbox'> <i>Remember me</i></label>";
                    echo "</fieldset>";
                    echo "<span><a href='#'>Forgot your password?</a></span>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                    echo "</li>";
                    echo "</ul>";
                    Session::setSessions('edituser', 0);
                }
                else
                {
                    include "userprofile.php";
                    Session::setSessions('edituser', 1);
                    Session::setSessions('userid', $edit);
                }
            ?>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="register">
    <div class="container">
        <?php
            include "connection.php";
            $edit = $_GET['edit'];
            if ($edit != 0)
            {
                $sql = "SELECT * FROM user WHERE uid='$edit'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_array($result);
                echo "<form action='new.php' method='POST' enctype='multipart/form-data'>";
                echo "<div class='register-top-grid'>";
                echo "<h1>PERSONAL INFORMATION</h1>";
                echo "<div>";
                echo "<span>Username<label>*</label></span>";
                echo "<input type='text' name='uname' id='uname' value='$row[1]'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Phone Number<label>*</label></span>";
                echo "<input type='text' name='phone' id='phone' value='$row[8]'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Email Address<label>*</label></span>";
                echo "<input type='text' name='email' id='email' value='$row[7]'>";
                echo "</div>";
                echo "<div>";
                echo "<span>City<label>*</label></span>";
                echo "<input type='text' name='city' id='city' value='$row[9]'>";
                echo "</div>";
                if($row[10] != 114514)
                {
                    echo "<div>";
                    echo "<span>Invitation Code (If have)<label></label></span>";
                    echo "<input type='text' name='icode' id='icode'>";
                    echo "</div>";
                }
                echo "<div>";
                echo "<span>Birthday<label>*</label></span>";
                echo "<input type='date' name='birth' id='birth' value='$row[4]'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Avatar<label>*</label></span>";
                echo "<input type='file' name='file' id='file' accept='image/png,image/jpg'>";
                echo "</div>";
                echo "<div class='clearfix'></div>";
                echo "<a class='news-letter' href='#'>";
                echo "<label><i> </i></label>";
                echo "</a>";
                echo "</div>";
                echo "<div class='register-bottom-grid'>";
                echo "<h4>CHANGE PASSWORD</h4>";
                echo "<div>";
                echo "<span> New Password<label>*</label></span>";
                echo "<input type='password' name='pwd1' id='pwd1' value='$row[2]'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Confirm New Password<label>*</label></span>";
                echo "<input type='password' name='pwd2' id='pwd2' value='$row[2]'>";
                echo "</div>";
                echo "<div class='clearfix'></div>";
                echo "</div>";
                echo "<div class='clearfix'></div>";
                echo "<div class='register-but'>";
                echo "<input type='submit' value='Submit' class='btn_3'>";
                echo "<div class='clearfix'></div>";
                echo "</div>";
                echo "</form>";
            }
            else
            {
                echo "<form action='new.php' method='POST' enctype='multipart/form-data'>";
                echo "<div class='register-top-grid'>";
                echo "<h1>PERSONAL INFORMATION</h1>";
                echo "<div>";
                echo "<span>Username<label>*</label></span>";
                echo "<input type='text' name='uname' id='uname'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Phone Number<label>*</label></span>";
                echo "<input type='text' name='phone' id='phone'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Email Address<label>*</label></span>";
                echo "<input type='text' name='email' id='email'>";
                echo "</div>";
                echo "<div>";
                echo "<span>City<label>*</label></span>";
                echo "<input type='text' name='city' id='city'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Invitation Code (If have)<label></label></span>";
                echo "<input type='text' name='icode' id='icode'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Birthday<label>*</label></span>";
                echo "<input type='date' name='birth' id='birth'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Avatar<label>*</label></span>";
                echo "<input type='file' name='file' id='file' accept='image/png,image/jpg'>";
                echo "</div>";
                echo "<div class='clearfix'></div>";
                echo "<a class='news-letter' href='#'>";
                echo "<label><i> </i></label>";
                echo "</a>";
                echo "</div>";
                echo "<div class='register-bottom-grid'>";
                echo "<h4>LOGIN INFORMATION</h4>";
                echo "<div>";
                echo "<span>Password<label>*</label></span>";
                echo "<input type='password' name='pwd1' id='pwd1'>";
                echo "</div>";
                echo "<div>";
                echo "<span>Confirm Password<label>*</label></span>";
                echo "<input type='password' name='pwd2' id='pwd2'>";
                echo "</div>";
                echo "<div class='clearfix'></div>";
                echo "</div>";
                echo "<div class='clearfix'></div>";
                echo "<div class='register-but'>";
                echo "<input type='submit' value='Submit' class='btn_3'>";
                echo "<div class='clearfix'></div>";
                echo "</div>";
                echo "</form>";
            }
        ?>
    </div>
</div>
<div class="grid_2">
    <div class="container">
        <div class="col-md-3 col_2">
            <h3>Music Management Website<br>Quick navigation panel</h3>
        </div>
        <div class="col-md-9 col_5">
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.html">R&B</a></li>
                    <li><a href="stock.html">HOUSE</a></li>
                    <li><a href="stock.html">Britpop</a></li>
                    <li><a href="stock.html">Trip-Hop</a></li>
                    <li><a href="stock.html">Gangsta Rap</a></li>
                    <li><a href="stock.html">Synth Pop</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.html">Orchestra</a></li>
                    <li><a href="stock.html">Chamber Pop</a></li>
                    <li><a href="stock.html">Folk</a></li>
                    <li><a href="stock.html">Bossa Nova</a></li>
                    <li><a href="stock.html">Classical Pop</a></li>
                    <li><a href="stock.html">Acappella</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.html">World Music</a></li>
                    <li><a href="stock.html">Dream-Pop</a></li>
                    <li><a href="stock.html">Electronic Music</a></li>
                    <li><a href="stock.html">Ambient</a></li>
                    <li><a href="stock.html">Dub</a></li>
                    <li><a href="stock.html">Punk</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.html">Black Metal</a></li>
                    <li><a href="stock.html">Chill Out</a></li>
                    <li><a href="stock.html">Minimalism</a></li>
                    <li><a href="stock.html">Jungle</a></li>
                    <li><a href="stock.html">Big-Beat</a></li>
                    <li><a href="stock.html">Breakbeat</a></li>
                </ul>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="grid_3">
    <div class="container">
        <ul id="footer-links">
            <li><a href="about.php">About Us</a></li>
        </ul>
        <p>Copyright © 2022 Music-Hub. All Rights Reserved.Design by <a href="#" target="_blank">Lalaland</a>
        </p>
    </div>
</div>
</body>
</html>