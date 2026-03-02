<!--Group Lalaland
Author: Tyron
-->
<?php
include "connection.php";
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland | Announcement</title>
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
            $sqlav = "SELECT avatar FROM user WHERE username='$unamegot'";
            $resultav = mysqli_query($conn, $sqlav);
            $rowav = mysqli_fetch_array($resultav);
            $sqldes = "SELECT uid FROM user WHERE username='$unamegot'";
            $resultdes = mysqli_query($conn, $sqldes);
            echo "<img src='$rowav[0]' class='img-responsive' alt=''/>";
            echo "</div>";
            echo "<div class='col-sm-7 col_6'>";
            echo "<h2>User Profile</h2>";
            echo "<br>";
            echo "<p class='movie_option'><strong>Messages : </strong>";
            $rowdes = mysqli_fetch_array($resultdes);
            $sql = "SELECT aid,time,content FROM announcement WHERE desuid='$rowdes[0]' limit 200";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) > 0)
            {
                while ($row = mysqli_fetch_row($result)) 
                {
                    $sqlsrc = "SELECT username FROM user JOIN announcement ON user.uid=announcement.srcuid WHERE aid='$row[0]'";
                    $resultsrc = mysqli_query($conn, $sqlsrc);
                    $rowsrc = mysqli_fetch_array($resultsrc);
                    echo "<li><td>($row[1])&nbsp$rowsrc[0]: &nbsp$row[2]<br></td></li>";
                }
            }
            else
            {
                echo "No messages yet.";
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