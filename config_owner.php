<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";   // ajuste se precisar
$pass = "";       // senha do seu MySQL
$db   = "guia_owners"; // banco novo

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão com guia_owners: " . $conn->connect_error);
}
?>