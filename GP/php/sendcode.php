<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$keyword = $_POST["keyword"];
$sqldes = "SELECT uid FROM user WHERE username='$keyword'";
$resultdes = mysqli_query($conn, $sqldes);
$rowdes = mysqli_fetch_array($resultdes);
$sqlsrc = "SELECT uid FROM user WHERE username='$unamegot'";
$resultsrc = mysqli_query($conn, $sqlsrc);
$rowsrc = mysqli_fetch_array($resultsrc);
date_default_timezone_set('Asia/Shanghai');
$time = date("Y-m-d H:i:s");
$url = "userAd.php";
if($keyword == "Send code to")
{
    header('Location:' . $url);
}
else
{
    $sqlinv = "SELECT iid, code FROM invitation ORDER BY rand() limit 1";
    $resultinv = mysqli_query($conn, $sqlinv);
    $rowinv = mysqli_fetch_array($resultinv);
    $sql = "INSERT INTO announcement(`aid`,`srcuid`,`desuid`,`time`,`content`) VALUES(null,'$rowsrc[0]','$rowdes[0]','$time','$rowinv[1]')";
    $result = mysqli_query($conn,$sql);
    header('Location:' . $url);
}

?>