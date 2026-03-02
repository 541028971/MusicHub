<?php

include "connection.php";
$sid = $_GET['sid'];
$pid = $_GET['pid'];
$sql = "DELETE FROM playlist_songs WHERE `playlist_songs`.`s_id` = $sid";
$result = mysqli_query($conn, $sql);
$url = "singleP.php?pid=$pid&&edit=1";
if ($result > 0) 
{
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>