<!--Group Lalaland
Author: Tyron
-->
<?php
include "connection.php";
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland | Favourite page</title>
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
            <h1><a href="indexL.php">Music Hub</a></h1>
        </div>
        <div class="top_right">
            <?php
            include "userprofile.php";
            ?>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="single">
<div class="col-md-2 stock_left">
    </div>
    <div class="container">
        <div class="single_box1">
            <?php
            echo "<div class='col-sm-5 single_left'>";
            $sql = "SELECT * FROM user WHERE username='$unamegot' limit 200";
            $result = mysqli_query($conn, $sql);
            $rowa = mysqli_fetch_array($result);
            echo "<img src='$rowa[3]' class='img-responsive' alt=''/>";
            echo "</div>";
            echo "<div class='col-sm-7 col_6'>";
            echo "<h2>User Profile</h2>";
            echo "<br>";
            echo "<p class='movie_option'><strong>Favourites : </strong>";
            $sqlf = "SELECT s_id,sname FROM songs JOIN favourite USING(s_id) JOIN user USING(uid) WHERE username='$unamegot'";
            $resultf = mysqli_query($conn, $sqlf);
            if(mysqli_num_rows($resultf) > 0)
            {
                while ($rowf = mysqli_fetch_row($resultf)) 
                {
                    echo "<li><td><a href='singleL.php?sname=$rowf[1]'>" . $rowf[1]. " </a><a href='unfavouritemusic.php?sid=$rowf[0]&&return=0'><font color='red'>Unfavourite</font></a></td></li>";
                }
            }
            else
            {
                echo "<br>";
                echo "<br>";
                echo "No favourite musics yet.<a href='stockL.php'> Go to all musics and find some?</a>";
            }

            echo "</p>";
            echo "<ul class='size'>";
            echo "<h3></h3>";
            echo "</ul>";
            echo "</div>";
            ?>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<div class="grid_3">
    <div class="container">
        <ul id="footer-links">
            <li><a href="aboutL.php">About Us</a></li>
        </ul>
        <p>Copyright © 2022 Music-Hub. All Rights Reserved.Design by <a href="#" target="_blank">Lalaland</a>
        </p>
    </div>
</div>
</body>
</html>