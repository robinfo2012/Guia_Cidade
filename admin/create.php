<?php
include 'config.php'; // seu arquivo de conexão com $mysqli

// Dados do dono que você quer cadastrar
$username = 'robinfonet@guia.com';        // usuário do dono
$password = 'eclesiac346';        // senha do dono
$business_name = 'Restaurante Z'; // nome do negócio que ele será vinculado

// 1️⃣ Procura o ID do negócio pelo nome
$stmt = $mysqli->prepare("SELECT id FROM businesses WHERE name=? LIMIT 1");
$stmt->bind_param('s', $business_name);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
    die("Negócio '$business_name' não encontrado. Cadastre o negócio primeiro.");
}
$business = $res->fetch_assoc();
$business_id = $business['id'];

// 2️⃣ Verifica se o usuário já existe
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows > 0){
    die("Usuário '$username' já existe.");
}

// 3️⃣ Cria o usuário dono vinculado ao negócio
$pass_hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (username, password, role, business_id) VALUES (?, ?, 'owner', ?)");
$stmt->bind_param('ssi', $username, $pass_hash, $business_id);
$stmt->execute();

echo "Usuário '$username' criado com sucesso e vinculado ao negócio '$business_name'.";
