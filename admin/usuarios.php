<?php
include "../config.php";

// CRUD Usuários
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Salvar usuário
if ($action === 'save' && !empty($_POST['username'])) {
    $username = trim($_POST['username']);
    $role = $_POST['role'] ?? 'owner';
    $password = $_POST['password'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE users SET username=?, role=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $role, $hash, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE users SET username=?, role=? WHERE id=?");
            $stmt->bind_param("ssi", $username, $role, $id);
        }
        $stmt->execute();
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
        $stmt->bind_param("sss", $username, $hash, $role);
        $stmt->execute();
    }
    header("Location: usuarios.php");
    exit;
}

// Excluir usuário
if ($action === 'delete' && $id) {
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: usuarios.php");
    exit;
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>👤 Usuários- Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<!-- Topo -->
<?php include "header.php"; ?>

<!--<div class="bg-white shadow p-4 flex justify-between items-center">
  <h1 class="text-xl font-bold text-blue-600">👤 Gestão de Usuários</h1>
  <div class="space-x-3">
    <a href="dashboard.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300'; ?>">🏠 Dashboard</a>    
   <a href="index.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">🏠 Negócios</a>
    <a href="usuarios.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">👤 Usuários</a>
    <a href="categorias.php" class="px-4 py-2 bg-blue-600 text-white rounded">📂 Categorias</a>
    <a href="settings.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">⚙ Configurações</a>
    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded">🚪 Sair</a>
  </div>
</div>-->

<div class="max-w-5xl mx-auto mt-6 bg-white shadow rounded-lg p-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">Lista de Usuários</h2>
    <a href="usuarios.php?action=new" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">➕ Novo Usuário</a>
  </div>

  <table class="w-full border-collapse">
    <thead>
      <tr class="bg-gray-200">
        <th class="p-2 border">ID</th>
        <th class="p-2 border">Usuário</th>
        <th class="p-2 border">Papel</th>
        <th class="p-2 border">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $res = $mysqli->query("SELECT * FROM users ORDER BY id DESC");
      while ($u = $res->fetch_assoc()):
      ?>
      <tr class="hover:bg-gray-50">
        <td class="p-2 border"><?php echo $u['id']; ?></td>
        <td class="p-2 border"><?php echo htmlspecialchars($u['username']); ?></td>
        <td class="p-2 border"><?php echo $u['role']; ?></td>
        <td class="p-2 border">
          <a href="usuarios.php?action=edit&id=<?php echo $u['id']; ?>" class="text-blue-600 hover:underline">Editar</a> |
          <a href="usuarios.php?action=delete&id=<?php echo $u['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Excluir este usuário?')">Excluir</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php if ($action === 'new' || $action === 'edit'):
  $user = ['id'=>0,'username'=>'','role'=>'owner'];
  if ($action === 'edit' && $id) {
      $stmt = $mysqli->prepare("SELECT * FROM users WHERE id=?");
      $stmt->bind_param("i",$id);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
  }
?>
<div class="max-w-lg mx-auto mt-6 bg-white shadow rounded-lg p-6">
  <h3 class="text-lg font-semibold mb-4"><?php echo $action==='new'?'Novo Usuário':'Editar Usuário'; ?></h3>
  <form method="post" action="usuarios.php?action=save" class="space-y-4">
    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
    <div>
      <label class="block text-sm font-medium">Usuário</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full border rounded p-2">
    </div>
    <div>
      <label class="block text-sm font-medium">Senha <?php if($action==='edit') echo '(deixe vazio para não alterar)';?></label>
      <input type="password" name="password" class="w-full border rounded p-2">
    </div>
    <div>
      <label class="block text-sm font-medium">Papel</label>
      <select name="role" class="w-full border rounded p-2">
        <option value="admin" <?php if($user['role']==='admin') echo 'selected';?>>Admin</option>
        <option value="editor" <?php if($user['role']==='editor') echo 'selected';?>>Editor</option>
        <option value="owner" <?php if($user['role']==='owner') echo 'selected';?>>Dono de Negócio</option>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
  </form>
</div>
<?php endif; ?>

</body>
<?php include "footer.php"; ?>
</html>
