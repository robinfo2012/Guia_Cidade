<?php
$mysqli = new mysqli('localhost','root','','guia_cidade');
if($mysqli->connect_errno){ die('Erro ao conectar: '.$mysqli->connect_error); }
$mysqli->set_charset('utf8mb4');
session_start();
?>
