<?php
require_once __DIR__ . "/../config_owner.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Verifica se email já existe
    $stmt = $conn->prepare("SELECT id FROM owners WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $message = "Este e-mail já está cadastrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO owners (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $message = "Cadastro realizado com sucesso! Agora faça login.";
        } else {
            $message = "Erro ao cadastrar. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro Dono de Negócio</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>Cadastro de Dono</h2>
  <?php if ($message): ?>
      <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>
  <form method="post">
      <label>Nome</label>
      <input type="text" name="name" required><br>

      <label>E-mail</label>
      <input type="email" name="email" required><br>

      <label>Senha</label>
      <input type="password" name="password" required><br>

      <button type="submit">Cadastrar</button>
  </form>
  <p>Já tem conta? <a href="owner_login.php">Entrar</a></p>
</body>
</html>