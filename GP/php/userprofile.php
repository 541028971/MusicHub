<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$sqlav = "SELECT avatar FROM user WHERE username='$unamegot'";
$resultav = mysqli_query($conn, $sqlav);
$rowav = mysqli_fetch_array($resultav);
$sqli = "SELECT identity FROM user WHERE username='$unamegot'";
$resulti = mysqli_query($conn, $sqli);
$rowi = mysqli_fetch_array($resulti);
echo "<ul>";
echo "<li class='login'>";
echo "<div id='loginContainer'>";
echo "<div id='loginButton' style='width:50px; height:50px; border-radius:100%; overflow:hidden;'><img src='$rowav[0]' width='50' height='50' /></div>";
echo "<div id='loginBox'>";
echo "<form id='loginForm'>";
echo "<fieldset id='body'>";
echo "<fieldset>";
echo "<span>&nbsp<font color='black'><b>$unamegot</b></font></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='dashboard.php' id='loginButton'>Dashboard</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='announcement.php' id='loginButton'>Announcement</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='stockL.php' id='loginButton'>All Musics</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='playlist.php' id='loginButton'>Playlists</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='favourite.php' id='loginButton'>Favourites</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='history.php' id='loginButton'>History</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='comment.php' id='loginButton'>Comment</a></span>";
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='feedback.php' id='loginButton'>Feedback</a></span>";
echo "</fieldset>";
echo "<fieldset>";
if ($rowi[0] == "Manager") {
    echo "<span><a href='userAd.php' id='loginButton'>Management</a></span>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<span><a href='upload.php?edit=0' id='loginButton'>Upload Music</a></span>";
} else if ($rowi[0] == "Creator") {
    echo "<span><a href='userAd.php' id='loginButton'>Creation</a></span>";
    echo "</fieldset>";
    echo "<fieldset>";
    echo "<span><a href='upload.php?edit=0' id='loginButton'>Upload Music</a></span>";
} 
else 
{
    echo "<span></span>";
}
echo "</fieldset>";
echo "<fieldset>";
echo "<span><a href='index.php' id='loginButton'>Log Out</a></span>";
echo "</fieldset>";
echo "</fieldset>";
echo "</form>";
echo "</div>";
echo "</div>";
echo "</li>";
echo "</ul>";
?>