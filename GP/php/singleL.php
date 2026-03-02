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
    <div class="container">
        <div class="single_box1">
            <?php
            echo "<div class='col-sm-5 single_left'>";
            date_default_timezone_set('Asia/Shanghai');
            $time = date("Y-m-d H:i:s");
            $sid = $_GET['sid'];
            $sqlUs = "SELECT uid, membership FROM user WHERE username='$unamegot'";
            $resultUs = mysqli_query($conn, $sqlUs);
            $rowUs = mysqli_fetch_array($resultUs);
            $sql = "SELECT * FROM songs WHERE s_id='$sid'";
            $result = mysqli_query($conn, $sql);
            $rowa = mysqli_fetch_array($result);
            Session::setSessions('songid', $rowa[0]);
            $sqlh = "INSERT INTO history(`uid`,`s_id`,`time`) VALUES ('$rowUs[0]','$rowa[0]','$time')";
            $resulth = mysqli_query($conn, $sqlh);
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
            $sqlf = "SELECT s_id FROM favourite JOIN user USING(uid) WHERE username='$unamegot'";
            $resultf = mysqli_query($conn, $sqlf);
            $favourited = false;
            while($rowf = mysqli_fetch_row($resultf))
            {
                if($rowf[0] == $rowa[0])
                {
                    $favourited = true;
                    break;
                }
            }
            while ($row = mysqli_fetch_row($result)) 
            {
                $fields = mysqli_num_fields($result);
                for ($f = 0; $f < $fields; $f++) 
                {
                    if ($f != 0 && $f != 4 && $f != 10 && $f != 11 && $f != 9) 
                    {
                        echo "<li><td>" . $row[$f] . "<br></td></li>";
                    }
                }
            }
            echo "Running time: ".(microtime());
            echo "</p>";
            echo "<br>";
            $sqlc = "SELECT username, content, time, good, bad, cid, identity FROM user JOIN comment USING(uid) JOIN songs USING(s_id) WHERE s_id='$rowa[0]' ORDER BY good desc limit 20";
            $resultc = mysqli_query($conn, $sqlc);
            echo "<p class='movie_option'><strong>Comments : </strong>";
            echo "<br>";
            echo "<br>";
            while ($rowc = mysqli_fetch_row($resultc)) 
            {
                $fieldc = mysqli_num_fields($resultc);
                if($rowc[0] == $unamegot)
                {
                    echo "<th>&nbsp&nbsp($rowc[2])&nbsp$rowc[0]: $rowc[1]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='good.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/good.png' alt=''/></a>&nbsp$rowc[3]&nbsp&nbsp&nbsp&nbsp<a href='bad.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/bad.png' alt=''/></a>&nbsp$rowc[4]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='deletecomment.php?cid=$rowc[5]'>Delete it</a><br></th>";
                }
                else if($rowc[0] != $unamegot && $rowi[0] == "Manager" && $rowc[6] == "Member")
                {
                    echo "<th>&nbsp&nbsp($rowc[2])&nbsp$rowc[0]: $rowc[1]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='good.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/good.png' alt=''/></a>&nbsp$rowc[3]&nbsp&nbsp&nbsp&nbsp<a href='bad.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/bad.png' alt=''/></a>&nbsp$rowc[4]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='deletecomment.php?cid=$rowc[5]'>Delete it</a><br></th>";
                }
                else if($rowc[0] != $unamegot && $rowi[0] == "Creator")
                {
                    echo "<th>&nbsp&nbsp($rowc[2])&nbsp$rowc[0]: $rowc[1]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='good.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/good.png' alt=''/></a>&nbsp$rowc[3]&nbsp&nbsp&nbsp&nbsp<a href='bad.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/bad.png' alt=''/></a>&nbsp$rowc[4]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='deletecomment.php?cid=$rowc[5]'>Delete it</a><br></th>";
                }
                else
                {
                    echo "<th>&nbsp&nbsp($rowc[2])&nbsp$rowc[0]: $rowc[1]&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='good.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/good.png' alt=''/></a>&nbsp$rowc[3]&nbsp&nbsp&nbsp&nbsp<a href='bad.php?cid=$rowc[5]'><img width='15px' height='15px' src='images/Icon/bad.png' alt=''/></a>&nbsp$rowc[4]<br></th>";
                }
                
            }
            echo "</p>";
            echo "<br>";
            $sqlp = "SELECT pid, pname FROM playlist WHERE uid='$rowUs[0]' AND pname != 'Favourite Songs'";
            $resultp = mysqli_query($conn, $sqlp);
            if (mysqli_num_rows($resultp) > 0)
            {
                echo "<form action='addlist.php' method='POST'>";
                echo "<select name='keyword'>";
                echo "<option>Add to playlists</option>";
                while($rowp = mysqli_fetch_row($resultp))
                {
                    echo "<option>$rowp[1]</option>";
                }
                echo "<option>Create new playlist...</option>";
                echo "</select>";
                echo "&nbsp&nbsp<input type='submit' value='Submit'>";
                echo "</form>";
            }
            echo "<ul class='size'>";
            echo "<h3></h3>";
            echo "<li><a href='$rowa[9]'>Music Links</a></li>";
            if($favourited == false)
            {
                echo "<li><a href='favouritemusic.php?sid=$rowa[0]'>Favourite</a></li>";
            }
            else
            {
                echo "<li><a href='unfavouritemusic.php?sid=$rowa[0]&&return=1'>Unfavourite</a></li>";
            }
            echo "<li><a href='stockL.php'>Back</a></li>";
            echo "</ul>";
            echo "</div>";
            if ($rowa[11] != "") 
            {
                if($rowUs[1] > 0)
                {
                    echo "<a class='btn_3' href='download.php?filename=$rowa[1]'>Download this Music</a>";
                }
            } 
            else 
            {
                echo "<a class='btn_3' href='priceL.php'>Download this Music</a>";
            }
            if ($rowi[0] != "Member") 
            {
                echo " <a class='btn_31' href='upload.php?edit=$rowa[0]'>Edit</a>";
                echo " <a class='btn_30' href='deleteM.php?sid=$rowa[0]'>Delete this Music</a>";
            }
            echo "<br>";
            if($rowUs[1] >= 0 && $rowUs[1] != 114514)
            {
                echo "$rowUs[1] download chances left.";
            }
            else if($rowUs[1] == 114514)
            {
                echo "<font color='green'>You are membership now, feel free to download.</font>";
            }
            else
            {
                echo "<font color='red'>No download chances left.</font>";
            }
            echo "<div class='clearfix'></div>";
            echo "<div>";
            echo "<br>";
            echo "<br>";
            echo "<br>";
            echo "<span>Comment<label></label></span>";
            echo "<form action='postcomment.php' method='POST'>";
            echo "<textarea name='comment' cols='125' rows='5'>Enter your comment here...</textarea>";
            echo "&nbsp&nbsp<input type='submit' value='Send'>";
            echo "</form>";
            echo "</div>";
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
                    while ($rowS = mysqli_fetch_array($resultS)) {
                        $counter++;
                        if ($counter < 5) {
                            echo "<li><a href='singleL.php?sid=$rowS[3]'>";
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
                    <li><a href="stockL.php">R&B</a></li>
                    <li><a href="stockL.php">HOUSE</a></li>
                    <li><a href="stockL.php">Britpop</a></li>
                    <li><a href="stockL.php">Trip-Hop</a></li>
                    <li><a href="stockL.php">Gangsta Rap</a></li>
                    <li><a href="stockL.php">Synth Pop</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stockL.php">Orchestra</a></li>
                    <li><a href="stockL.php">Chamber Pop</a></li>
                    <li><a href="stockL.php">Folk</a></li>
                    <li><a href="stockL.php">Bossa Nova</a></li>
                    <li><a href="stockL.php">Classical Pop</a></li>
                    <li><a href="stockL.php">Acappella</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stockL.php">World Music</a></li>
                    <li><a href="stockL.php">Dream-Pop</a></li>
                    <li><a href="stockL.php">Electronic Music</a></li>
                    <li><a href="stockL.php">Ambient</a></li>
                    <li><a href="stockL.php">Dub</a></li>
                    <li><a href="stockL.php">Punk</a></li>
                </ul>
            </div>
            <div class="col_1_of_5 span_1_of_5">
                <ul class="list1">
                    <li><a href="stockL.php">Black Metal</a></li>
                    <li><a href="stockL.php">Chill Out</a></li>
                    <li><a href="stockL.php">Minimalism</a></li>
                    <li><a href="stockL.php">Jungle</a></li>
                    <li><a href="stockL.php">Big-Beat</a></li>
                    <li><a href="stockL.php">Breakbeat</a></li>
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