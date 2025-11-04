<?php
// Conexão com MySQL
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guia_cidade';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die('Erro ao conectar no MySQL: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// Funções de configuração
function getSetting($name){
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT value FROM settings WHERE name=? LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($value);
    if($stmt->fetch()) return $value;
    return null;
}

function setSetting($name, $value){
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO settings (name, value) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE value=?");
    $stmt->bind_param("sss", $name, $value, $value);
    $stmt->execute();
}
