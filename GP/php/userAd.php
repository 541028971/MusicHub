<!--Group Lalaland
Author: Tyron
-->
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland |Stock page</title>
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
    <script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#horizontalTab').easyResponsiveTabs
            (
                {
                    type: 'default', //Types: default, vertical, accordion
                    width: 'auto', //auto or any width like 600px
                    fit: true   // 100% fit in a container
                }
            );
        });
    </script>
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
<div class="stock_box">
    <div class="col-md-2 stock_left">
        <?php
        $sqlu = "SELECT uid, username FROM user WHERE membership!=114514 AND identity='Member'";
        $resultu = mysqli_query($conn, $sqlu);
        $sqli = "SELECT identity FROM user WHERE username='$unamegot'";
        $resulti = mysqli_query($conn, $sqli);
        $rowi = mysqli_fetch_array($resulti);
        echo "<div class='w_sidebar'>";
        echo "<section class='sky-form'>";
        echo "<div class='col col-4'>";
        if($rowi[0] == 'Creator')
        {
            echo "<h4><a href='generatecode.php'>Generate Code</a></h4>";
        }
        echo "<form action='sendcode.php' method='POST'>";
        echo "<select name='keyword'>";
        echo "<option><h4>Send code to</h4></option>";
        while($rowu = mysqli_fetch_row($resultu))
        {
            echo "<option><h4>$rowu[1]</h4></option>";
        }
        echo "</select>";
        echo "&nbsp&nbsp<input type='submit' value='Submit'>";
        echo "</form>";
        echo "<br>";
        echo "</div>";
        echo "</section>";
        echo "</div>";
        ?>
    </div>
    <div class="col-md-10 sap_tabs">
        <div class="clearfix"></div>
        <?php
        include "connection.php";
        $unamegot = Session::getSessions('uname');
        $sqli = "SELECT identity FROM user WHERE username='$unamegot'";
        $resulti = mysqli_query($conn, $sqli);
        $rowi = mysqli_fetch_array($resulti);
        if ($rowi[0] == "Creator") {
            $sql = "SELECT username, phone_number, avatar FROM user ORDER BY uid limit 200";
        } else {
            $sql = "SELECT username, phone_number, avatar FROM user WHERE identity='Member' ORDER BY uid limit 200";
        }
        $result = mysqli_query($conn, $sql);
        $number = mysqli_num_rows($result);
        echo "<div id='horizontalTab' style='display: block; width: 100%; margin: 0px;'>";
        echo "<ul class='resp-tabs-list'>";
        if ($rowi[0] == "Creator") 
        {
            echo "<li class='resp-tab-item' aria-controls='tab_item-0' role='tab'><span>All Users</span></li>";
        } 
        else 
        {
            echo "<li class='resp-tab-item' aria-controls='tab_item-0' role='tab'><span>All Members</span></li>";
        }
        echo "<div class='clearfix'></div>";
        echo "Running time: ".(microtime());
        echo "</ul>";
        echo "<div class='resp-tabs-container'>";
        echo "<div class='tab-1 resp-tab-content' aria-labelledby='tab_item-0'>";
        if (mysqli_num_rows($result) > 0) {
            $fields = mysqli_num_fields($result);
            echo "<ul class='tab_img'>";
            while ($row = mysqli_fetch_array($result)) {
                echo "<li><a href='singleAd.php?uname=$row[0]'>";
                echo "<img src='$row[2]' class='img-responsive' alt=''/>";
                echo "<div class='tab_desc'>";
                if ($row[0] == $unamegot) {
                    echo "<p>$row[0](You)</p>";
                } else {
                    echo "<p>$row[0]</p>";
                }
                echo "<h4>$row[1]</h4>";
                echo "</div>";
                echo "</a></li>";
            }
            echo "<div class='clearfix'></div>";
            echo "</ul>";
        } else {
            echo "No results found.";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";
        ?>
    </div>
    <div class="clearfix"></div>
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
            <li><a href="aboutL.php">About Us</a></li>
        </ul>
        <p>Copyright © 2022 Music-Hub. All Rights Reserved.Design by <a href="#" target="_blank">Lalaland</a>
        </p>
    </div>
</div>
</body>
</html>