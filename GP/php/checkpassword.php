<?php
include "connection.php";
include "session.php";
$user = $_POST["uname"];
$pwd = $_POST["password"];
$sql = "SELECT * FROM user WHERE username='$user' AND password='$pwd' limit 1";
$result = mysqli_query($conn, $sql);
$identity = "SELECT identity FROM user WHERE username='$user'";
$ridentity = mysqli_query($conn, $identity);
$row1 = mysqli_fetch_array($ridentity);
$status = "SELECT status FROM user WHERE username='$user'";
$rstatus = mysqli_query($conn, $status);
$row2 = mysqli_fetch_array($rstatus);
Session::setSessions('uname', $user);
if ($row2[0] == "Banned") 
{
    echo "Sorry, you are banned by the creator.";
    header("Refresh:3.5;url=index.php");
} else if ($row2[0] == "Disabled") 
{
    echo "Sorry, you are disabled from the server.";
    header("Refresh:3.5;url=index.php");
} else {
    if (mysqli_num_rows($result) > 0 && $row1[0] == "Manager") {
        header("location:indexL.php");
    } else if (mysqli_num_rows($result) > 0 && $row1[0] == "Member") {
        //$sqld = "UPDATE booklist SET status='disabled' WHERE status='enabled'";
        //$resultd = mysqli_query($conn, $sqld);
        //$sqla = "UPDATE booklist SET amount='0'";
        //$resulta = mysqli_query($conn, $sqla);
        header("location:indexL.php");
    } else if (mysqli_num_rows($result) > 0 && $row1[0] == "Creator") {
        header("location:indexL.php");
    } else {
        echo "Data not found in the database, this may be because the username does not exist or the password is incorrect.";
        header("Refresh:3.5;url=index.php");
        die();
    }
}
?>