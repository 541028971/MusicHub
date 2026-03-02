<?php
include "connection.php";
include "session.php";
$unamegot = Session::getSessions('uname');
$edit = Session::getSessions('editplaylist');
$pname = $_POST["pname"];
$sqlp = "SELECT pname FROM playlist";
$resultp = mysqli_query($conn, $sqlp);
$fieldsp = mysqli_num_fields($resultp);
$sqlu = "SELECT uid FROM user WHERE username='$unamegot'";
$resultu = mysqli_query($conn, $sqlu);
$rowu = mysqli_fetch_array($resultu);
$private = true;
if(empty($_POST["checkbox"]))
{
    $private = false;
};
header("Content-Type: text/html; charset=UTF-8");
$upload = $_FILES['file'];
if ($upload['name'] == "") 
{
    echo "Warning, the playlist cover cannot be null.";
    header("Refresh:3.5;url=createplaylist.php?edit=0");
} 
else 
{
    if ($upload['size'] > 8000000) 
    {
        echo "Failed, the size exceeds the limit.";
        header("Refresh:3.5;url=createplaylist.php?edit=0");
    } 
    else 
    {
        if ($pname != "") 
        {
            $upload['name'] = $pname . ".png";
            $link = "images/Playlist/" . $upload['name'];
            if ($edit == 0) 
            {
                if($private == false)
                {
                    $sql = "INSERT INTO `playlist` (`pid`, `uid`, `pname`, `pcover`, `private`, `views`) VALUES (null, '$rowu[0]', '$pname', '$link', 0, 0);";
                }
                else
                {
                    $sql = "INSERT INTO `playlist` (`pid`, `uid`, `pname`, `pcover`, `private`, `views`) VALUES (null, '$rowu[0]', '$pname', '$link', 1, 0);";
                }
            } 
            else 
            {
                $pid = Session::getSessions('playlisteditid');
                $upload['name'] = $pname . ".png";
                $link = "images/Playlist/" . $upload['name'];
                if($private == false)
                {
                    $sql = "UPDATE `playlist` SET `pname`='$pname', `pcover`='$link', `private`=0 WHERE pid='$pid';";
                }
                else
                {
                    $sql = "UPDATE `playlist` SET `pname`='$pname', `pcover`='$link', `private`=1 WHERE pid='$pid';";
                }
            }
            $result = mysqli_query($conn, $sql);
            if ($result > 0) 
            {
                //$sqld = "UPDATE booklist SET status='disabled' WHERE status='enabled'";
                //$resultd = mysqli_query($conn, $sqld);
                //$sqla = "UPDATE booklist SET amount='0'";
                //$resulta = mysqli_query($conn, $sqla);
                //copy($upload['tmp_name'], 'Img/' . $upload['name']);
                copy($upload['tmp_name'], 'images/Playlist/' . $upload['name']);
                header("Refresh:0;url=playlist.php");
            } 
            else 
            {
                echo "Failed.";
                header("Refresh:3.5;url=createplaylist.php?edit=0");
            }
        } 
        else 
        {

            echo "Warning, there is no playlist title.";
            header("Refresh:3.5;url=createplaylist.php?edit=0");
        }
    }
}
?>