<?php

include "connection.php";
include "session.php";
$sid = Session::getSessions('songid');
$cid = $_GET['cid'];
$sql = "UPDATE comment SET bad=bad+1 WHERE cid='$cid'";
$result = mysqli_query($conn, $sql);
$sqlsname = "SELECT sname FROM songs WHERE s_id='$sid'";
$resultsname = mysqli_query($conn, $sqlsname);
$rowsname = mysqli_fetch_array($resultsname);
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