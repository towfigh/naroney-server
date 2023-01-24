<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-headers: *");


$host = 'localhost';
$user = 'rygysdzw_nn_root';
$pass = 'nar@saj#shams';
$db = 'rygysdzw_naroney';

global $conn;
$conn = new mysqli($host, $user, $pass, $db);

$charset = 'SET CHARACTER SET utf8';
$conn->query($charset);

if ($conn->connect_error) {
    die("connection failed : $conn->connect_error");
}


?>