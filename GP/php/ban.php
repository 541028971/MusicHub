<?php

include "connection.php";

$uid = $_GET['uid'];

$sql = "UPDATE user SET status='Banned' WHERE uid='$uid'";
$sqlU = "SELECT username FROM user WHERE uid='$uid'";
$result = mysqli_query($conn, $sql);
$resultU = mysqli_query($conn, $sqlU);
$rowU = mysqli_fetch_array($resultU);
$url = "singleAd.php?uname=$rowU[0]";
if ($result > 0) {
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>