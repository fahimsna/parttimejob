<?php
$conn = mysqli_connect("localhost", "root", "", "parttimejob");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>