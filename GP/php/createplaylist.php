<!--Group Lalaland
Author: Tyron
-->
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland | Playlist Create</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="keywords" content="Photo-Hub Responsive web template, Bootstrap Web Templates, Flat Web Templates, Andriod Compatible web template,
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design"/>
    <script type="application/x-javascript"> addEventListener("load", function () 
    {
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
<div class="register">
    <div class="container">
        <?php
        include "connection.php";
        $edit = $_GET['edit'];
        if($edit != 0)
        {
            Session::setSessions('editplaylist', 1);
        }
        else
        {
            Session::setSessions('editplaylist', 0);
        }
        if ($edit != 0) 
        {
            $sql = "SELECT * FROM playlist WHERE pid='$edit'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_array($result);
            Session::setSessions('playlisteditid', $edit);
            echo "<form action='newplaylist.php' method='POST' enctype='multipart/form-data'>";
            echo "<div class='register-top-grid'>";
            echo "<h1>PLAYLIST INFO</h1>";
            echo "<div>";
            echo "<span>Playlist Name<label>*</label></span>";
            echo "<input type='text' name='pname' id='pname' value='$row[2]'>";
            echo "</div>";
            echo "<div>";
            echo "<span>Playlist Cover<label>*</label></span>";
            echo "<input type='file' name='file' id='file' accept='image/jpg,image/png'>";
            echo "</div>";
            echo "<div class='clearfix'></div>";
            echo "<a class='news-letter' href='#'>";
            echo "<label><i><input type='checkbox' id='checkbox' name='checkbox'> Private </i></label>";
            echo "</a>";
            echo "</div>";
            echo "<div class='register-bottom-grid'>";
            echo "</div>";
            echo "<div>";
            echo "<input type='submit' value='Submit' class='btn_3'>";
            echo "<div class='clearfix'></div>";
            echo "</div>";
            echo "</form>";
        } 
        else 
        {
            echo "<form action='newplaylist.php' method='POST' enctype='multipart/form-data'>";
            echo "<div class='register-top-grid'>";
            echo "<h1>PLAYLIST INFO</h1>";
            echo "<div>";
            echo "<span>Playlist Name<label>*</label></span>";
            echo "<input type='text' name='pname' id='pname'>";
            echo "</div>";
            echo "<div>";
            echo "<span>Playlist Cover<label>*</label></span>";
            echo "<input type='file' name='file' id='file' accept='image/jpg,image/png'>";
            echo "</div>";
            echo "<div class='clearfix'></div>";
            echo "<a class='news-letter' href='#'>";
            echo "<label><i><input type='checkbox' id='checkbox' name='checkbox'> Private </i></label>";
            echo "</a>";
            echo "</div>";
            echo "<div class='register-bottom-grid'>";
            echo "</div>";
            echo "<div>";
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