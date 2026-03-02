<?php

include "connection.php";
$pid = $_GET['pid'];
$sqlps = "DELETE FROM playlist_songs WHERE playlist_songs.pid=$pid";
$resultps = mysqli_query($conn, $sqlps);
$sql = "DELETE FROM playlist WHERE `playlist`.`pid` = $pid";
$result = mysqli_query($conn, $sql);
$url = "playlist.php";
if ($result > 0) 
{
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>