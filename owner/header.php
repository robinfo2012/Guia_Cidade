<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['owner_id'])) {
    header("Location: owner_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel do Dono</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f8f9fa; }
    header {
      background: #1e3a8a; /* azul escuro */
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 { margin: 0; font-size: 20px; }
    nav a {
      color: white;
      margin-left: 20px;
      text-decoration: none;
      font-weight: bold;
    }
    nav a:hover {
      text-decoration: underline;
    }
    main {
      padding: 20px;
    }
  </style>
</head>
<body>

<header>
  <h1>📊 Painel do Dono</h1>
  <nav>
    <a href="business_list.php">🏢 Minha Empresa</a>
    <a href="business_form.php">✏️ Editar Empresa</a>
    <a href="pagamento.php">💳 Pagamentos</a>
    <a href="logout.php">🚪 Sair</a>
  </nav>
</header>

<main>