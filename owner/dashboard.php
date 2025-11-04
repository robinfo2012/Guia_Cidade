<?php
session_start();
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
</head>
<body>
  <h2>Bem-vindo, <?= htmlspecialchars($_SESSION['owner_name']) ?>!</h2>
  <nav>
      <a href="business_form.php">Cadastrar/Editar Empresa</a> |
      <a href="logout.php">Sair</a>
  </nav>
</body>
</html>