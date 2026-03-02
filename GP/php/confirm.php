<?php
    include "connection.php";
    $username = $_POST["uname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $sql = "SELECT password FROM user WHERE username='$username' AND email='$email' AND phone_number='$phone'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_array($result);
        echo "Your password is $row[0]";
        header("Refresh:3.5;url=index.php");
    }
?>