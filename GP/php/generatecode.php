<?php
include "connection.php";
$array[0] = 10000;
$url = "userAd.php";
for($i = 0;$i < 1000;$i++)
{
    $duplicated = true;
    $array[$i] = mt_rand(10000,99999);
}
for($i = 0;$i < 1000;$i++)
{
    $sql = "INSERT INTO invitation(`iid`,`code`) VALUES (null, '$array[$i]')";
    $result = mysqli_query($conn,$sql);
}
header('Location:' . $url);
?>