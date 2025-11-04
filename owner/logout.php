<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Remove todas as variáveis de sessão
$_SESSION = [];

// Destrói a sessão
session_destroy();

// Redireciona para a página de login
header('Location: login.php');
exit;
