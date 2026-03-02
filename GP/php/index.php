<!--Group Lalaland
Author: Tyron
-->
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland | Main page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="keywords" content="Photo-Hub Responsive web template, Bootstrap Web Templates, Flat Web Templates, Andriod Compatible web template,
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design"/>
    <script type="application/x-javascript"> addEventListener("load", function () {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() {
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
            <ul>
                <li><a href="register.php?edit=0">Register</a></li>
                |
                <li class="login">
                    <div id="loginContainer"><a href="#" id="loginButton"><span>Login</span></a>
                        <div id="loginBox">
                            <form id="loginForm" action="checkpassword.php" method="POST">
                                <fieldset id="body">
                                    <fieldset>
                                        <label for="username">Username</label>
                                        <input type="text" name="uname" id="uname">
                                    </fieldset>
                                    <fieldset>
                                        <label for="password">Password</label>
                                        <input type="password" name="password" id="password">
                                    </fieldset>
                                    <input type="submit" id="login" value="Sign in">
                                    <label for="checkbox"><input type="checkbox" id="checkbox"> <i>Remember me</i>
                                    </label>
                                </fieldset>
                                <span><a href="forget.php">Forgot your password?</a></span>
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="banner">
    <div class="container">
        <?php
        include "connection.php";
        $sql = "SELECT sname,download,s_id FROM songs WHERE download != '' AND (s_id=11 OR s_id=14 OR s_id=22 OR s_id=23) ORDER BY rand() limit 1";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        echo "<a href='single.php?sid=$row[2]'><font color='white'>Now playing : " . $row[0] . "</font></a>";
        echo "<embed src='$row[1]' controls=playbutton height=1px width=1px type=audio/mp3 loop=true autostart=true>";
        ?>
        <div class="span_1_of_1">
            <h2>Listen to what you think<br>Travel the world.</h2>
            <div class="search">
                <ul class="nav1">
                    <li id="search">
                        <form action="search.php" method="POST">
                            <input type="text" name="search_text" id="search_text"
                                   placeholder="Search for title, album, style or arrangement..."/>
                            <input type="submit" name="search_button" id="search_button" value="">
                        </form>
                    </li>
                    <li id="options">
                        <a href="stock.php">All Musics</a>
                        <!--<ul class="subnav">
                            <li><a href="#">singer</a></li>
                            <li><a href="#">Arrangement</a></li>
                            <li><a href="#">Album</a></li>
                        </ul>-->
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="grid_1">
    <h3>More than a dozen kinds of music for you to choose from</h3>
    <br>
    <div class="col-md-2 col_1">
        <h4>Pop</h4>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(1).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(2).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(3).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(4).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(5).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <h4>Rock</h4>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(6).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(7).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(8).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(9).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(10).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <h4>Folk</h4>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(11).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(12).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(13).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(14).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(15).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <h4>Electronic</h4>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(16).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(17).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(18).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(19).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="col-md-2 col_1">
        <img src="images/grid%20(20).jpg" class="img-responsive" alt=""/>
    </div>
    <div class="clearfix"></div>
</div>
<div class="grid_2">
    <div class="container">
        <div class="col-md-3 col_2">
            <h3>Music<br>Navigation panel</h3>
        </div>
        <div class="col-md-9 col_5">
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.php">R&B</a></li>
                    <li><a href="stock.php">HOUSE</a></li>
                    <li><a href="stock.php">Britpop</a></li>
                    <li><a href="stock.php">Trip-Hop</a></li>
                    <li><a href="stock.php">Gangsta Rap</a></li>
                    <li><a href="stock.php">Synth Pop</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.php">Orchestra</a></li>
                    <li><a href="stock.php">Chamber Pop</a></li>
                    <li><a href="stock.php">Folk</a></li>
                    <li><a href="stock.php">Bossa Nova</a></li>
                    <li><a href="stock.php">Classical Pop</a></li>
                    <li><a href="stock.php">Acappella</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.php">World Music</a></li>
                    <li><a href="stock.php">Dream-Pop</a></li>
                    <li><a href="stock.php">Electronic Music</a></li>
                    <li><a href="stock.php">Ambient</a></li>
                    <li><a href="stock.php">Dub</a></li>
                    <li><a href="stock.php">Punk</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stock.php">Black Metal</a></li>
                    <li><a href="stock.php">Chill Out</a></li>
                    <li><a href="stock.php">Minimalism</a></li>
                    <li><a href="stock.php">Jungle</a></li>
                    <li><a href="stock.php">Big-Beat</a></li>
                    <li><a href="stock.php">Breakbeat</a></li>
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