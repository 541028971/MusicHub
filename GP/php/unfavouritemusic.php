<?php

include "connection.php";
$sid = $_GET['sid'];
$return = $_GET['return'];
$sqls = "SELECT sname FROM songs WHERE s_id='$sid'";
$results = mysqli_query($conn, $sqls);
$rows = mysqli_fetch_array($results);
$sql = "DELETE FROM favourite WHERE `favourite`.`s_id` = $sid";
$result = mysqli_query($conn, $sql);
if($return == 1)
{
    $url = "singleL.php?sid=$sid";
}
else
{
    $url = "favourite.php";
}
if ($result > 0) 
{
    header('Location:' . $url);
} else {
    echo "<script> alert('update failed.') </script> ";
    header('Location:' . $url);
}


?>