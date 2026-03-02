<?php

include "connection.php";
$sid = $_GET['sid'];
$sqlps = "DELETE FROM playlist_songs WHERE playlist_songs.s_id='$sid'";
$resultps = mysqli_query($conn, $sqlps);
$sqlh = "DELETE FROM history WHERE history.s_id='$sid'";
$resulth = mysqli_query($conn, $sqlh);
$sqlf = "DELETE FROM favourite WHERE favourite.s_id='$sid'";
$resultf = mysqli_query($conn, $sqlf);
$sqlc = "DELETE FROM comment WHERE comment.s_id='$sid'";
$resultc = mysqli_query($conn, $sqlc);
$sql = "DELETE FROM songs WHERE `songs`.`s_id` = $sid";
$result = mysqli_query($conn, $sql);
$url = "stockL.php";
if ($result > 0) {
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>