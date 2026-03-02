<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$sid = Session::getSessions('songid');
date_default_timezone_set('Asia/Shanghai');
$time = date("Y-m-d H:i:s");
$comment = $_POST["comment"];
$sqlu = "SELECT uid FROM user WHERE username='$unamegot'";
$resultu = mysqli_query($conn, $sqlu); 
$rowu = mysqli_fetch_array($resultu);
$sqlsname = "SELECT sname FROM songs WHERE s_id='$sid'";
$resultsname = mysqli_query($conn, $sqlsname);
$rowsname = mysqli_fetch_array($resultsname);
$url = "singleL.php?sid=$sid";
$sql = "INSERT INTO `comment`(`cid`,`uid`,`s_id`,`content`,`good`,`bad`,`time`) VALUES (null, '$rowu[0]', '$sid', '$comment', 0, 0, '$time')";
$result = mysqli_query($conn, $sql); 
header('Location:' . $url);
?>