<?php
include "connection.php";
include "session.php";
$user = $_POST["uname"];
$pwd1 = $_POST["pwd1"];
$pwd2 = $_POST["pwd2"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$birth = $_POST["birth"];
$city = $_POST["city"];
if(empty($_POST["icode"]))
{
    $icode = 0;
}
else
{
    $icode = 1;
    $icodec = $_POST["icode"];
}
//$icode = $_POST["icode"];
$sqlu = "SELECT username FROM user";
$resultu = mysqli_query($conn, $sqlu);
$fieldsu = mysqli_num_fields($resultu);
$edit = Session::getSessions('edituser');
$uid = Session::getSessions('userid');
Session::setSessions('uname', $user);
header("Content-Type: text/html; charset=UTF-8");
$upload = $_FILES['file'];
if ($pwd1 == $pwd2) 
{
    if ($upload['name'] == "") 
    {
        echo "Warning, the avatar cannot be null.";
        header("Refresh:3.5;url=register.php?edit=0");
    } 
    else 
    {
        if ($user != "") 
        {
            if ($email == "") 
            {
                echo "Warning, the email cannot be null.";
                header("Refresh:3.5;url=register.php?edit=0");
            } 
            else 
            {
                if ($city == "") 
                {
                    echo "Warning, the city cannot be null.";
                    header("Refresh:3.5;url=register.php?edit=0");
                } 
                else 
                {
                    if ($phone == "") 
                    {
                        echo "Warning, the phone number cannot be null.";
                        header("Refresh:3.5;url=register.php?edit=0");
                    } 
                    else 
                    {
                        if ($birth == "") 
                        {
                            echo "Warning, the birthday cannot be null.";
                            header("Refresh:3.5;url=register.php?edit=0");
                        } 
                        else 
                        {
                            $upload['name'] = $user . ".png";
                            $link = "images/Avatar/" . $upload['name'];
                            if($edit != 1)
                            {
                                if($icode == 0)
                                {
                                    $sql = "INSERT INTO `user` (`uid`, `username`, `password`, `avatar`, `birth`, `identity`, `status`, `email`, `phone_number`, `city`, `membership`) VALUES (8, '$user', '$pwd1', '$link','$birth','Member', 'Enabled', '$email', '$phone', '$city', 5);";
                                }
                                else
                                {
                                    $sqlcodeq = "SELECT code FROM invitation WHERE code='$icodec'";
                                    $resultcodeq = mysqli_query($conn,$sqlcodeq);
                                    if(mysqli_num_rows($resultcodeq) > 0)
                                    {
                                        $sqlcoded = "DELETE FROM invitation WHERE code='$icodec'";
                                        $resultcoded = mysqli_query($conn,$sqlcoded);
                                        $sql = "INSERT INTO `user` (`uid`, `username`, `password`, `avatar`, `birth`, `identity`, `status`, `email`, `phone_number`, `city`, `membership`) VALUES (null, '$user', '$pwd1', '$link','$birth','Member', 'Enabled', '$email', '$phone', '$city', 114514);";
                                    }
                                    else
                                    {
                                        echo "Warning, the invatation code is incorrect.";
                                        header("Refresh:3.5;url=register.php?edit=0");
                                        die();
                                    }
                                }
                                $result = mysqli_query($conn, $sql);
                                $sqlu = "SELECT uid FROM user WHERE username='$user'";
                                $resultu = mysqli_query($conn, $sqlu);
                                $rowu = mysqli_fetch_array($resultu);
                                $sqlf = "INSERT INTO `playlist`(`pid`,`uid`,`pname`,`pcover`,`private`) VALUES (null,'$rowu[0]','Favourite Songs','images/Playlist/Favourite.png','1')";
                                $resultf = mysqli_query($conn, $sqlf);
                            }
                            else
                            {
                                if($icode == 0)
                                {
                                    $sql = "UPDATE `user` SET `username`='$user', `password`='$pwd1', `avatar`='$link', `birth`='$birth', `email`='$email', `phone_number`='$phone', `city`='$city' WHERE uid='$uid';";
                                }
                                else
                                {                                    
                                    $sqlcodeq = "SELECT code FROM invitation WHERE code='$icodec'";
                                    $resultcodeq = mysqli_query($conn,$sqlcodeq);
                                    if(mysqli_num_rows($resultcodeq) > 0)
                                    {
                                        $sqlcoded = "DELETE FROM invitation WHERE code='$icodec'";
                                        $resultcoded = mysqli_query($conn,$sqlcoded);
                                        $sql = "UPDATE `user` SET `username`='$user', `password`='$pwd1', `avatar`='$link', `birth`='$birth', `email`='$email', `phone_number`='$phone', `city`='$city', `membership`=114514 WHERE uid='$uid';";
                                    }
                                    else
                                    {
                                        echo "Warning, the invatation code is incorrect.";
                                        header("Refresh:3.5;url=register.php?edit=0");
                                        die();
                                    }
                                }
                                $result = mysqli_query($conn,$sql);
                            }
                            if ($result > 0) 
                            {
                                copy($upload['tmp_name'], 'images/Avatar/' . $upload['name']);
                                header("Refresh:0;url=indexL.php");
                            } 
                            else 
                            {
                                echo "Failed.";
                                header("Refresh:3.5;url=register.php?edit=0");
                            }
                        }
                    }
                }
            }
        } else {
            echo "Warning, there is no username.";
            header("Refresh:3.5;url=register.php?edit=0");
        }
    }
} else if ($pwd1 == "") {
    echo "Warning, there is no password.";
    header("Refresh:3.5;url=register.php?edit=0");
} else {
    echo "Warning, the passwords are inconsistent.";
    header("Refresh:3.5;url=register.php?edit=0");
}
?>