<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$keyword = $_POST["keyword"];
$feedback = $_POST["feedback"];
$sqldes = "SELECT uid FROM user WHERE username='$keyword'";
$resultdes = mysqli_query($conn, $sqldes);
$rowdes = mysqli_fetch_array($resultdes);
$sqlsrc = "SELECT uid FROM user WHERE username='$unamegot'";
$resultsrc = mysqli_query($conn, $sqlsrc);
$rowsrc = mysqli_fetch_array($resultsrc);
date_default_timezone_set('Asia/Shanghai');
$time = date("Y-m-d H:i:s");
$url = "feedback.php";
if($keyword == "Send feedback to")
{
    header('Location:' . $url);
}
else
{
    $sql = "INSERT INTO feedback(`fid`,`srcuid`,`desuid`,`time`,`content`) VALUES(null,'$rowsrc[0]','$rowdes[0]','$time','$feedback')";
    $result = mysqli_query($conn,$sql);
    header('Location:' . $url);
}

?>