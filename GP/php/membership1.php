<?php

include "connection.php";
include "session.php";
$uid = $_GET['uid'];
$unamegot = Session::getSessions('uname');
$sql = "UPDATE user SET membership=114514 WHERE uid='$uid'";
$sqlU = "SELECT username FROM user WHERE uid='$uid'";
$result = mysqli_query($conn, $sql);
$resultU = mysqli_query($conn, $sqlU);
$rowU = mysqli_fetch_array($resultU);
$url = "singleAd.php?uname=$rowU[0]";
if ($result > 0) 
{
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>