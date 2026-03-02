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
        $(document).ready(function () 
        {
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
        <div class="w_sidebar">
            <section class="sky-form">
                <h4>Collections</h4>
                <div class="col col-4">
                    <label class="checkbox"><input type="checkbox" name="checkbox" checked=""><i></i>All images</label>
                </div>
                <div class="col col-4">
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Standard</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Following</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Lorem Ipsum</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Injected humour</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Default model </label>
                </div>
            </section>
            <section class="sky-form">
                <h4>Freshness</h4>
                <div class="col col-4">
                    <label class="checkbox"><input type="checkbox" name="checkbox" checked=""><i></i>Any time</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Past 3 months</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Past month</label>
                </div>
                <div class="col col-4">
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Past week</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Past 3 days</label>
                    <label class="checkbox"><input type="checkbox" name="checkbox"><i></i>Any time</label>
                </div>
            </section>
            <section class="sky-form">
                <h4>color</h4>
                <ul class="w_nav2">
                    <li><a class="color1" href="#"></a></li>
                    <li><a class="color2" href="#"></a></li>
                    <li><a class="color3" href="#"></a></li>
                    <li><a class="color4" href="#"></a></li>
                    <li><a class="color5" href="#"></a></li>
                    <li><a class="color6" href="#"></a></li>
                    <li><a class="color7" href="#"></a></li>
                    <li><a class="color8" href="#"></a></li>
                    <li><a class="color9" href="#"></a></li>
                    <li><a class="color10" href="#"></a></li>
                    <li><a class="color12" href="#"></a></li>
                    <li><a class="color13" href="#"></a></li>
                    <li><a class="color14" href="#"></a></li>
                    <li><a class="color15" href="#"></a></li>
                    <li><a class="color5" href="#"></a></li>
                    <li><a class="color6" href="#"></a></li>
                    <li><a class="color7" href="#"></a></li>
                    <li><a class="color8" href="#"></a></li>
                    <li><a class="color9" href="#"></a></li>
                    <li><a class="color10" href="#"></a></li>
                </ul>
            </section>
            <section class="sky-form">
                <h4>discount</h4>
                <div class="col col-4">
                    <label class="radio"><input type="radio" name="radio" checked=""><i></i>60 % and above</label>
                    <label class="radio"><input type="radio" name="radio"><i></i>50 % and above</label>
                    <label class="radio"><input type="radio" name="radio"><i></i>40 % and above</label>
                </div>
                <div class="col col-4">
                    <label class="radio"><input type="radio" name="radio"><i></i>30 % and above</label>
                    <label class="radio"><input type="radio" name="radio"><i></i>20 % and above</label>
                    <label class="radio"><input type="radio" name="radio"><i></i>10 % and above</label>
                </div>
            </section>
        </div>
    </div>
    <div class="col-md-10 sap_tabs">
        <div id="horizontalTab" style="display: block; width: 100%; margin: 0px;">
            <ul class="resp-tabs-list">
                <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><span>What's Hot</span></li>
                <li class="resp-tab-item" aria-controls="tab_item-1" role="tab"><span>Signers</span></li>
                <li class="resp-tab-item" aria-controls="tab_item-2" role="tab"><span>Song Title</span></li>
                <li class="resp-tab-item" aria-controls="tab_item-3" role="tab"><span>Last Updated</span></li>
                <div class="clearfix"></div>
            </ul>
            <div class="resp-tabs-container">
                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-0">
                    <?php
                    include "connection.php";
                    echo "Running time: ".(microtime());
                    $sql = "SELECT sname, album, cover,s_id FROM songs ORDER BY views desc limit 200";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        $fields = mysqli_num_fields($result);
                        echo "<ul class='tab_img'>";
                        while ($row = mysqli_fetch_array($result)) 
                        {
                            $length0 = strlen($row[0]);
                            $length1 = strlen($row[1]);
                            echo "<li><a href='singleL.php?sid=$row[3]'>";
                            echo "<img src='$row[2]' class='img-responsive' alt=''/>";
                            echo "<div class='tab_desc'>";
                            if($length0 > 21)
                            {
                                $temp0 = "";
                                for($i = 0;$i < 21;$i++)
                                {
                                    $temp0[$i] = $row[0][$i];
                                }
                                $temp0[21] = ".";
                                $temp0[22] = ".";
                                $temp0[23] = ".";
                                echo "<p>$temp0</p>";
                            }
                            else
                            {
                                echo "<p>$row[0]</p>";
                            }
                            if($length1 > 28)
                            {
                                $temp1 = "";
                                for($i = 0;$i <28;$i++)
                                {
                                    $temp1[$i] = $row[1][$i];
                                }
                                $temp1[28] = ".";
                                $temp1[29] = ".";
                                $temp1[30] = ".";
                                echo "<h4>$temp1</h4>";
                            }
                            else
                            {
                                echo "<h4>$row[1]</h4>";
                            }
                            echo "</div>";
                            echo "</a></li>";
                        }
                        echo "<div class='clearfix'></div>";
                        echo "</ul>";
                    } else {
                        echo "No results found.";
                    }
                    ?>
                </div>
                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-1">
                    <?php
                    include "connection.php";
                    $sql = "SELECT sname, album, cover,s_id FROM songs ORDER BY arrangement limit 200";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        $fields = mysqli_num_fields($result);
                        echo "<ul class='tab_img'>";
                        while ($row = mysqli_fetch_array($result)) 
                        {
                            $length0 = strlen($row[0]);
                            $length1 = strlen($row[1]);
                            echo "<li><a href='singleL.php?sid=$row[3]'>";
                            echo "<img src='$row[2]' class='img-responsive' alt=''/>";
                            echo "<div class='tab_desc'>";
                            if($length0 > 21)
                            {
                                $temp0 = "";
                                for($i = 0;$i < 21;$i++)
                                {
                                    $temp0[$i] = $row[0][$i];
                                }
                                $temp0[21] = ".";
                                $temp0[22] = ".";
                                $temp0[23] = ".";
                                echo "<p>$temp0</p>";
                            }
                            else
                            {
                                echo "<p>$row[0]</p>";
                            }
                            if($length1 > 28)
                            {
                                $temp1 = "";
                                for($i = 0;$i <28;$i++)
                                {
                                    $temp1[$i] = $row[1][$i];
                                }
                                $temp1[28] = ".";
                                $temp1[29] = ".";
                                $temp1[30] = ".";
                                echo "<h4>$temp1</h4>";
                            }
                            else
                            {
                                echo "<h4>$row[1]</h4>";
                            }
                            echo "</div>";
                            echo "</a></li>";
                        }
                        echo "<div class='clearfix'></div>";
                        echo "</ul>";
                    } else {
                        echo "No records found.";
                    }
                    ?>
                </div>
                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-2">
                    <?php
                    include "connection.php";
                    $sql = "SELECT sname, album, cover, s_id FROM songs ORDER BY sname limit 200";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) 
                    {
                        $fields = mysqli_num_fields($result);
                        echo "<ul class='tab_img'>";
                        while ($row = mysqli_fetch_array($result)) 
                        {
                            $length0 = strlen($row[0]);
                            $length1 = strlen($row[1]);
                            echo "<li><a href='singleL.php?sid=$row[3]'>";
                            echo "<img src='$row[2]' class='img-responsive' alt=''/>";
                            echo "<div class='tab_desc'>";
                            if($length0 > 21)
                            {
                                $temp0 = "";
                                for($i = 0;$i < 21;$i++)
                                {
                                    $temp0[$i] = $row[0][$i];
                                }
                                $temp0[21] = ".";
                                $temp0[22] = ".";
                                $temp0[23] = ".";
                                echo "<p>$temp0</p>";
                            }
                            else
                            {
                                echo "<p>$row[0]</p>";
                            }
                            if($length1 > 28)
                            {
                                $temp1 = "";
                                for($i = 0;$i <28;$i++)
                                {
                                    $temp1[$i] = $row[1][$i];
                                }
                                $temp1[28] = ".";
                                $temp1[29] = ".";
                                $temp1[30] = ".";
                                echo "<h4>$temp1</h4>";
                            }
                            else
                            {
                                echo "<h4>$row[1]</h4>";
                            }
                            echo "</div>";
                            echo "</a></li>";
                        }
                        echo "<div class='clearfix'></div>";
                        echo "</ul>";
                    } else {
                        echo "No records found.";
                    }
                    ?>
                </div>
                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-3">
                    <?php
                    include "connection.php";
                    $sql = "SELECT sname, album, cover, s_id FROM songs ORDER BY s_id desc limit 200";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) 
                    {
                        $fields = mysqli_num_fields($result);
                        echo "<ul class='tab_img'>";
                        while ($row = mysqli_fetch_array($result)) 
                        {
                            $length0 = strlen($row[0]);
                            $length1 = strlen($row[1]);
                            echo "<li><a href='singleL.php?sid=$row[3]'>";
                            echo "<img src='$row[2]' class='img-responsive' alt=''/>";
                            echo "<div class='tab_desc'>";
                            if($length0 > 21)
                            {
                                $temp0 = "";
                                for($i = 0;$i < 21;$i++)
                                {
                                    $temp0[$i] = $row[0][$i];
                                }
                                $temp0[21] = ".";
                                $temp0[22] = ".";
                                $temp0[23] = ".";
                                echo "<p>$temp0</p>";
                            }
                            else
                            {
                                echo "<p>$row[0]</p>";
                            }
                            if($length1 > 28)
                            {
                                $temp1 = "";
                                for($i = 0;$i <28;$i++)
                                {
                                    $temp1[$i] = $row[1][$i];
                                }
                                $temp1[28] = ".";
                                $temp1[29] = ".";
                                $temp1[30] = ".";
                                echo "<h4>$temp1</h4>";
                            }
                            else
                            {
                                echo "<h4>$row[1]</h4>";
                            }
                            echo "</div>";
                            echo "</a></li>";
                        }
                        echo "<div class='clearfix'></div>";
                        echo "</ul>";
                    } 
                    else 
                    {
                        echo "No records found.";
                    }
                    ?>
                </div>
            </div>
        </div>
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