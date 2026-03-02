<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$sqlu = "SELECT uid FROM user WHERE username='$unamegot'";
$resultu = mysqli_query($conn, $sqlu);
$rowu = mysqli_fetch_array($resultu);
$keyword = $_POST["keyword"];
$sid = Session::getSessions('songid');
$sqlp = "SELECT pid FROM playlist WHERE pname='$keyword' AND uid='$rowu[0]'";
$resultp = mysqli_query($conn, $sqlp);
$rowp = mysqli_fetch_array($resultp);
$sqls = "SELECT s_id FROM playlist_songs WHERE pid='$rowp[0]'";
$results = mysqli_query($conn, $sqls);
$sqlsname = "SELECT sname FROM songs WHERE s_id='$sid'";
$resultsname = mysqli_query($conn, $sqlsname);
$rowsname = mysqli_fetch_array($resultsname);
$dupliated = false;
$url1 = "createplaylist.php?edit=0";
$url2 = "singleL.php?sid=$sid";
if($keyword == "Create new playlist")
{
    header('Location:' . $url1);
}
else if($keyword == "Add to playlists")
{
    header('Location:' . $url2);
}
else
{
    while($rows = mysqli_fetch_row($results))
    {
        if($rows[0] == $sid)
        {
            $dupliated = true;
            break;
        }
    }
    if($dupliated == true)
    {
        header('Location:' . $url2);
    }
    else
    {
        $sql = "INSERT INTO `playlist_songs`(`pid`,`s_id`) VALUES ('$rowp[0]','$sid')";
        $result = mysqli_query($conn, $sql);
        header('Location:' . $url2);
    }
}

?>