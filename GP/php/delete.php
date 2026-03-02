<?php

include "connection.php";
$uid = $_GET['uid'];
$sqlp = "SELECT pid FROM playlist WHERE uid = '$uid'";
$resultp = mysqli_query($conn, $sqlp);
while($rowp = mysqli_fetch_row($resultp))
{
    $sqlps = "DELETE FROM playlist_songs WHERE `playlist_songs`.`pid` = '$rowp[0]'";
    $resultps = mysqli_query($conn, $sqlps);
}
$sqla = "DELETE FROM announcement WHERE announcement.srcuid='$uid' OR announcement.desuid='$uid'";
$resulta = mysqli_query($conn, $sqla);
$sqlfe = "DELETE FROM feedback WHERE feedback.desuid='$uid' OR feedback.srcuid='$uid'";
$resultfe = mysqli_query($conn, $sqlfe);
$sqlf = "DELETE FROM favourite WHERE `favourite`.`uid` = $uid";
$resultf = mysqli_query($conn, $sqlf);
$sqlp2 = "DELETE FROM playlist WHERE `playlist`.`uid` = $uid";
$resultp2 = mysqli_query($conn, $sqlp2);
$sqlh = "DELETE FROM history WHERE `history`.`uid` = $uid";
$resulth = mysqli_query($conn, $sqlh);
$sqlc = "DELETE FROM comment WHERE `comment`.`uid` = $uid";
$resultc = mysqli_query($conn, $sqlc);
$sql = "DELETE FROM user WHERE `user`.`uid` = $uid";
$result = mysqli_query($conn, $sql);
$url = "userAd.php";
if ($result > 0) {
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>