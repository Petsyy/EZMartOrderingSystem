<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ez_mart";

try {

    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {

    die("Connection failed: " . $e->getMessage());
}
