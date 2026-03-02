<!--Group Lalaland
Author: Tyron
-->
<?php
include "connection.php";
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland |Goods page</title>
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
                                        <input type="text" name="uname" id="uanme">
                                    </fieldset>
                                    <fieldset>
                                        <label for="password">Password</label>
                                        <input type="password" name="password" id="password">
                                    </fieldset>
                                    <input type="submit" id="login" value="Sign in">
                                    <label for="checkbox"><input type="checkbox" id="checkbox"> <i>Remember
                                            me</i></label>
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
<div class="single">
    <div class="container">
        <div class="single_box1">
            <?php
            echo "<div class='col-sm-5 single_left'>";
            $sid = $_GET['sid'];
            $sql = "SELECT * FROM songs WHERE s_id='$sid'";
            $result = mysqli_query($conn, $sql);
            $rowa = mysqli_fetch_array($result);
            echo "<img src='$rowa[4]' class='img-responsive' alt=''/>";
            echo "</div>";
            echo "<div class='col-sm-7 col_6'>";
            echo "<h3>Music Profile</h3>";
            echo "<br>";
            echo "<p class='movie_option'><strong>Info : </strong>";
            $sql = "SELECT * FROM songs WHERE s_id='$sid'";
            $sqlU = "UPDATE songs SET views=views+1 WHERE s_id='$sid'";
            $result = mysqli_query($conn, $sql);
            $resultU = mysqli_query($conn, $sqlU);
            while ($row = mysqli_fetch_row($result)) {
                $fields = mysqli_num_fields($result);
                for ($f = 0; $f < $fields; $f++) {
                    if ($f != 0 && $f != 4 && $f != 10 && $f != 11 && $f != 9) {
                        echo "<li><td>" . $row[$f] . "<br></td></li>";
                    }
                }
            }
            echo "Running time: ".(microtime());
            echo "</p>";
            echo "<ul class='size'>";
            echo "<h3></h3>";
            echo "<li><a href='$rowa[9]'>Music Links</a></li>";
            echo "<li><a href='stock.php'>Back</a></li>";
            echo "</ul>";
            echo "</div>";
            echo "<div class='clearfix'></div>";
            ?>
        </div>
        <div class="tags">
            <h4 class="tag_head">Similar Music</h4>
            <ul class="tags_images">
                <?php
                $sqlS = "SELECT sname, album, cover,s_id FROM songs WHERE stype='$rowa[6]' AND sname != '$rowa[1]' ORDER BY rand() limit 4";
                $resultS = mysqli_query($conn, $sqlS);
                if (mysqli_num_rows($resultS) > 0) {
                    $counter = 0;
                    $fields = mysqli_num_fields($resultS);
                    echo "<ul class='tab_img'>";
                    while ($rowS = mysqli_fetch_array($resultS)) 
                    {
                        $counter++;
                        if ($counter < 5) 
                        {
                            echo "<li><a href='single.php?sid=$rowS[3]'>";
                            echo "<img src='$rowS[2]' class='img-responsive' alt=''/>";
                            echo "<div class='tab_desc'>";
                            echo "<p>$rowS[0]</p>";
                            echo "<h4>$rowS[1]</h4>";
                            echo "</div>";
                            echo "</a></li>";
                        } else {
                            break;
                        }
                    }
                    echo "<div class='clearfix'></div>";
                    echo "</ul>";
                } else {
                    echo "No similar found.";
                }
                ?>
            </ul>
        </div>
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