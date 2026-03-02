<?php
include "getInfor.php";
$title = "这是一个标题";
$type = array("html", "css", "js", "php");
$data = "这是一条数据";
$content = "这是内容";
?>

<!--这里是前端代码-->

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
</head>
<body>
<ul>
    <?php foreach ($type as $value) {
        echo "<li>$value</li>";
    } ?>
</ul>
<h1><?php echo $data; ?></h1>


<div><?php echo $content; ?></div>
</body>
</html>