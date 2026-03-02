<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$sql = "SELECT membership FROM user WHERE username='$unamegot'";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_array($result);
if($row[0] != 114514)
{
    $new = $row[0] - 1;
    $sqlU = "UPDATE user SET `membership`='$new' WHERE username='$unamegot'";
    $resultU = mysqli_query($conn,$sqlU);
}

$filenametemp = $_GET['filename'];
$filename = $filenametemp . ".mp3";
$download_path = "audios/Download/";

if (preg_match("\.\.", $filename)) die("Sorry, you cannot download it.");
$file = str_replace("..", "", $filename);

if (preg_match("\.ht.+", $filename)) die("Sorry, you cannot download it.");

$file = "$download_path$file";

if (!file_exists($file)) die("File not exist.");

$type = filetype($file);

$today = date("F j, Y, g:i a");
$time = time();

header("Content-type: $type");
header("Content-Disposition: attachment;filename=$filename");
header("Content-Transfer-Encoding: binary");
header('Pragma: no-cache');
header('Expires: 0');
set_time_limit(0);
readfile($file);
?>