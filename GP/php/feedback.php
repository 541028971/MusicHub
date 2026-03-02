<!--Group Lalaland
Author: Tyron
-->
<?php
include "connection.php";
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland | Feedback</title>
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
            $sqli = "SELECT identity FROM user WHERE username='$unamegot'";
            $resulti = mysqli_query($conn, $sqli);
            $rowi = mysqli_fetch_array($resulti);
            $sqlav = "SELECT avatar FROM user WHERE username='$unamegot'";
            $resultav = mysqli_query($conn, $sqlav);
            $rowav = mysqli_fetch_array($resultav);
            $sqldes = "SELECT uid FROM user WHERE username='$unamegot'";
            $resultdes = mysqli_query($conn, $sqldes);
            $rowdes = mysqli_fetch_array($resultdes);
            echo "<img src='$rowav[0]' class='img-responsive' alt=''/>";
            echo "</div>";
            if($rowi[0] != "Member")
            {
                echo "<div class='col-sm-7 col_6'>";
                echo "<h2>User Profile</h2>";
                echo "<br>";
                echo "<p class='movie_option'><strong>Messages : </strong>";
                $sql = "SELECT fid,time,content FROM feedback WHERE desuid='$rowdes[0]' limit 200";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0)
                {
                    while ($row = mysqli_fetch_row($result)) 
                    {
                        $sqlsrc = "SELECT username FROM user JOIN feedback ON user.uid=feedback.srcuid WHERE fid='$row[0]'";
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
            }
            if($rowi[0] != "Creator")
            {
                echo "<div>";
                echo "<form action='sendfeedback.php' method='POST'>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<br>";
                echo "<select name='keyword'>";
                echo "<option><h4>Send feedback to</h4></option>";
                if($rowi[0] == "Member")
                {
                    $sqlad = "SELECT uid,username FROM user WHERE identity!='Member'";
                }
                else
                {
                    $sqlad = "SELECT uid,username FROM user WHERE identity='Creator'";
                }
                $resultad = mysqli_query($conn, $sqlad);
                while($rowad = mysqli_fetch_row($resultad))
                {
                    echo "<option><h4>$rowad[1]</h4></option>";
                }
                echo "</select>";
                echo "<textarea name='feedback' cols='125' rows='5'>Enter your feedback here...</textarea>";
                echo "&nbsp&nbsp<input type='submit' value='Send'>";
                echo "</form>";
                echo "</div>";
            }
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