<?php
require "../config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare("SELECT id, password, role FROM users WHERE username=? AND role='admin' LIMIT 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($u = $res->fetch_assoc()) {
        if (password_verify($pass, $u['password'])) {
            $_SESSION['admin_id'] = $u['id'];
            $_SESSION['role'] = $u['role'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Usuário ou senha inválidos!";
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Login Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white shadow-lg rounded-lg p-8 w-96">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Painel Admin</h2>
    <?php if (!empty($error)): ?>
      <p class="text-red-600 text-sm mb-4 text-center"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post">
      <label class="block mb-3">
        <span class="text-gray-700">Usuário</span>
        <input type="text" name="username" class="w-full mt-1 border rounded p-2" required>
      </label>
      <label class="block mb-6">
        <span class="text-gray-700">Senha</span>
        <input type="password" name="password" class="w-full mt-1 border rounded p-2" required>
      </label>
      <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Entrar</button>
    </form>
  </div>
</body>
</html>
