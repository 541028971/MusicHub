<?php

include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$sid = $_GET['sid'];
$sqlu = "SELECT uid FROM user WHERE username='$unamegot'";
$resultu = mysqli_query($conn, $sqlu);
$rowu = mysqli_fetch_array($resultu);
$sqls = "SELECT sname FROM songs WHERE s_id='$sid'";
$results = mysqli_query($conn, $sqls);
$rows = mysqli_fetch_array($results);
$sql = "INSERT INTO favourite(`uid`,`s_id`) VALUES ('$rowu[0]','$sid');";
$result = mysqli_query($conn, $sql);
$url = "singleL.php?sid=$sid";
if ($result > 0) {
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>