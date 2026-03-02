<?php
include "connection.php";
include "session.php";
$song = $_POST["sname"];
$album = $_POST["album"];
$link = $_POST["link"];
$type = $_POST["type"];
$arrangement = $_POST["arrangement"];
$release = $_POST["release"];
$intro = $_POST["intro"];
$lyrics = $_POST["lyrics"];
$sqlS = "SELECT sname FROM songs";
$resultS = mysqli_query($conn, $sqlS);
$fieldsS = mysqli_num_fields($resultS);
$edit = Session::getSessions('editsongs');
$sid = Session::getSessions('songsid');
header("Content-Type: text/html; charset=UTF-8");
$upload1 = $_FILES['file1'];
$upload2 = $_FILES['file2'];
if ($upload1['name'] == "") 
{
    echo "Warning, the cover cannot be null.";
    header("Refresh:3.5;url=upload.php?edit=0");
} 
else 
{
    if ($upload1['size'] > 8000000) 
    {
        echo "Failed, the size exceeds the limit.";
        header("Refresh:3.5;url=upload.php?edit=0");
    } 
    else 
    {
        if ($song != "") 
        {
            if ($album == "") 
            {
                echo "Warning, the album cannot be null.";
                header("Refresh:3.5;url=upload.php?edit=0");
            } 
            else 
            {
                if ($link == "") 
                {
                    echo "Warning, the link cannot be null.";
                    header("Refresh:3.5;url=upload.php?edit=0");
                } 
                else 
                {
                    if ($type == "") 
                    {
                        echo "Warning, the song type cannot be null.";
                        header("Refresh:3.5;url=upload.php?edit=0");
                    } 
                    else 
                    {
                        if ($arrangement == "") 
                        {
                            echo "Warning, the arrangement cannot be null.";
                            header("Refresh:3.5;url=upload.php?edit=0");
                        } 
                        else 
                        {
                            if ($release == "") 
                            {
                                echo "Warning, the release time cannot be null.";
                                header("Refresh:3.5;url=upload.php?edit=0");
                            } 
                            else 
                            {
                                $upload1['name'] = $song . ".png";
                                $link2 = "images/Cover/" . $upload1['name'];
                                if ($edit == 0) 
                                {
                                    if ($upload2['name'] == "" && $lyrics != "" && $intro != "") {
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', '$lyrics','$link2','$arrangement', '$type', '$intro', '$release', '$link', 10 , null);";
                                    } 
                                    else if ($upload2['name'] != "" && $lyrics == "" && $intro != "") {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', default,'$link2','$arrangement', '$type', '$intro', '$release', '$link', 10 ,'$link3');";
                                    } 
                                    else if ($upload2['name'] != "" && $lyrics != "" && $intro == "") {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', '$lyrics','$link2','$arrangement', '$type', default, '$release', '$link', 10 ,'$link3');";
                                    } 
                                    else if ($upload2['name'] == "" && $lyrics == "" && $intro != "") {
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', default,'$link2','$arrangement', '$type', '$intro', '$release', '$link', 10 ,null);";
                                    } 
                                    else if ($upload2['name'] != "" && $lyrics == "" && $intro == "") {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', default,'$link2','$arrangement', '$type', default, '$release', '$link', 10 ,'$link3');";
                                    } 
                                    else if ($upload2['name'] == "" && $lyrics != "" && $intro == "") {
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', '$lyrics','$link2','$arrangement', '$type', default, '$release', '$link', 10 ,null);";
                                    } 
                                    else if ($upload2['name'] == "" && $lyrics == "" && $intro == "") {
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', default,'$link2','$arrangement', '$type', default, '$release', '$link', 10 ,null);";
                                    } 
                                    else 
                                    {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES (41, '$song', '$album', '$lyrics','$link2','$arrangement', '$type', '$intro', '$release', '$link', 10 ,'$link3');";
                                    }
                                } 
                                else 
                                {
                                    if ($upload2['name'] == "" && $lyrics != "" && $intro != "") 
                                    {
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `lyrics` = '$lyrics', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `sintroduction` = '$intro', `release_time` = '$release', `link` = '$link' WHERE s_id='$sid';";
                                    } 
                                    else if ($upload2['name'] != "" && $lyrics == "" && $intro != "") 
                                    {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `sintroduction` = '$intro', `release_time` = '$release', `link` = '$link', `download` = '$link3' WHERE s_id='$sid';";
                                    } 
                                    else if ($upload2['name'] != "" && $lyrics != "" && $intro == "") 
                                    {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album',`lyrics` = '$lyrics', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `release_time` = '$release', `link` = '$link', `download` = '$link3' WHERE s_id='$sid';";
                                    } 
                                    else if ($upload2['name'] == "" && $lyrics == "" && $intro != "") 
                                    {
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `sintroduction` = '$intro', `release_time` = '$release', `link` = '$link' WHERE s_id='$sid';";
                                    } 
                                    else if ($upload2['name'] != "" && $lyrics == "" && $intro == "") 
                                    {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `release_time` = '$release', `link` = '$link', download` = '$link3' WHERE s_id='$sid';";
                                    } 
                                    else if ($upload2['name'] == "" && $lyrics != "" && $intro == "") 
                                    {
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `lyrics` = '$lyrics', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `release_time` = '$release', `link` = '$link' WHERE s_id='$sid';";
                                    } 
                                    else if ($upload2['name'] == "" && $lyrics == "" && $intro == "") 
                                    {
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `release_time` = '$release', `link` = '$link' WHERE s_id='$sid';";
                                    } 
                                    else 
                                    {
                                        $upload2['name'] = $song . ".mp3";
                                        $link3 = "audios/Download/" . $upload2['name'];
                                        $sql = "UPDATE `songs` SET `sname` = '$song', `album` = '$album', `lyrics` = '$lyrics', `cover` = '$link2', `arrangement` = '$arrangement', `stype` = '$type', `sintroduction` = '$intro', `release_time` = '$release', `link` = '$link', `download` = '$link3' WHERE s_id='$sid';";
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
                                    copy($upload1['tmp_name'], 'images/Cover/' . $upload1['name']);
                                    if ($upload2['name'] != "") {
                                        copy($upload2['tmp_name'], 'audios/Download/' . $upload2['name']);
                                    }
                                    header("Refresh:0;url=stockL.php");
                                } 
                                else 
                                {
                                    echo "Failed.";
                                    header("Refresh:3.5;url=upload.php?edit=0");
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo "Warning, there is no song title.";
            header("Refresh:3.5;url=upload.php?edit=0");
        }
    }
}
?>