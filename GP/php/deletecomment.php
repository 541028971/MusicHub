<?php
include "connection.php";
include "session.php";
$sid = Session::getSessions('songid');
$cid = $_GET['cid'];
$sql = "DELETE FROM comment WHERE `comment`.`cid` = '$cid'";
$result = mysqli_query($conn, $sql);
$url = "singleL.php?sid=$sid";
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