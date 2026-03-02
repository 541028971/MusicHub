<?php

include "connection.php";
include "session.php";
$uid = $_GET['uid'];
$unamegot = Session::getSessions('uname');
$sqli = "SELECT identity FROM user WHERE username='$unamegot'";
$resulti = mysqli_query($conn, $sqli);
$rowi = mysqli_fetch_array($resulti);
$sql = "UPDATE user SET identity='Manager' WHERE uid='$uid'";
$sqlU = "SELECT username FROM user WHERE uid='$uid'";
$result = mysqli_query($conn, $sql);
$resultU = mysqli_query($conn, $sqlU);
$rowU = mysqli_fetch_array($resultU);
if($rowi[0] == "Manager")
{
    $url = "userAd.php";
}
else
{
    $url = "singleAd.php?uname=$rowU[0]";
}

if ($result > 0) 
{
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>