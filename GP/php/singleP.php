<!--Group Lalaland
Author: Tyron
-->
<?php
include "connection.php";
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Lalaland |Playlist detailed page</title>
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
            $pid = $_GET['pid'];
            $edit = $_GET['edit'];
            $sqlp = "SELECT pcover,pname FROM playlist WHERE pid='$pid'";
            $resultp = mysqli_query($conn, $sqlp);
            $rowp = mysqli_fetch_array($resultp);
            $sqlf = "SELECT s_id, sname FROM songs JOIN favourite USING(s_id) JOIN user USING(uid) WHERE username='$unamegot'";
            $resultf = mysqli_query($conn, $sqlf);
            echo "<img src='$rowp[0]' class='img-responsive' alt=''/>";
            echo "</div>";
            echo "<div class='col-sm-7 col_6'>";
            echo "<h3>Playlist Profile</h3>";
            echo "<br>";
            $sql = "SELECT s_id,sname,pname FROM songs JOIN playlist_songs USING(s_id) JOIN playlist USING(pid) WHERE pid='$pid'";
            $result = mysqli_query($conn, $sql);
            $favourite = false;
            if($edit == 0)
            {
                $sqlU = "UPDATE playlist SET views=views+1 WHERE pid='$pid'";
                $result = mysqli_query($conn, $sql);
                $resultU = mysqli_query($conn, $sqlU);
            }
            if($rowp[1] != "Favourite Songs")
            {
                if(mysqli_num_rows($result) > 0)
                {
                    echo "<h3 class='movie_option'><strong>Musics : </strong>";
                    echo "<h3> </h3>";
                    while ($row = mysqli_fetch_row($result)) 
                    {
                        if($edit ==1)
                        {
                            echo "<li><td><a href='singleL.php?sid=$row[0]'>" . $row[1] . "</a>&nbsp&nbsp<a href='deletemusic.php?sid=$row[0]&&pid=$pid'><font color='red'>Delete It</font></a><br></td></li>";
                        }
                        else
                        {
                            echo "<li><td><a href='singleL.php?sid=$row[0]'>" . $row[1] . "</a></td></li>";
                        }
                    }
                    if($edit == 1)
                    {
                        echo "<li><a href='stockL.php'>Add</a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a href='playlist.php'>Back</a></li>";
                    }
                }
                else
                {
                    if($edit == 1)
                    {
                        echo "No musics found. <a href='stockL.php?'>Add some</a> or<a href='playlist.php'> back to playlists.</a>";
                    }
                    else
                    {
                        echo "No musics found. <a href='playlistsquare.php'> Back to playlist square.</a>";
                    }
                }

            }
            else
            {
                if(mysqli_num_rows($resultf) > 0)
                {
                    echo "<h3 class='movie_option'><strong>Musics : </strong>";
                    echo "<h3> </h3>";
                    while ($rowf = mysqli_fetch_row($resultf)) 
                    {
                        if($edit ==1)
                        {
                            echo "<li><td><a href='singleL.php?sid=$rowf[0]'>" . $rowf[1] . "<br></td></li>";
                        }
                        else
                        {
                            echo "<li><td><a href='singleL.php?sid=$rowf[0]'>" . $rowf[1] . "</a></td></li>";
                        }
                    }
                    if($edit == 1)
                    {
                        echo "<li><a href='playlist.php'>Back</li>";
                    }
                }
                else
                {
                    echo "No musics found. <a href='stockL.php'>Add some</a> or<a href='playlist.php'> back to playlists.</a>";
                }
            }
            echo "<div class='clearfix'></div>";
            echo "Running time: ".(microtime());
            ?>
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