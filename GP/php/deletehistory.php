<?php

include "connection.php";
$time = $_GET['time'];
$uid = $_GET['uid'];
$sql = "DELETE FROM history WHERE `history`.`time` = '$time' AND `history`.`uid` = '$uid'";
$result = mysqli_query($conn, $sql);
$url = "history.php";
if ($result > 0) 
{
    header('Location:' . $url);
} 
else 
{
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>