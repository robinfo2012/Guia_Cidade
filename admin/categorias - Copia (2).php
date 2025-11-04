<?php
include "../config.php";
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Salvar categoria
if ($action === 'save' && !empty($_POST['name'])) {
    $name = trim($_POST['name']);
    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
    header("Location: categorias.php");
    exit;
}

// Excluir categoria
if ($action === 'delete' && $id > 0) {
    $stmt = $mysqli->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: categorias.php");
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Categorias - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<!-- Topo -->
<div class="bg-white shadow p-4 flex justify-between items-center">
  <h1 class="text-xl font-bold text-blue-600">📂 Gestão de Categorias</h1>
  <div class="space-x-3">
    <a href="index.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">🏠 Negócios</a>
    <a href="usuarios.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">👤 Usuários</a>
    <a href="categorias.php" class="px-4 py-2 bg-blue-600 text-white rounded">📂 Categorias</a>
    <a href="settings.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">⚙ Configurações</a>
    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded">🚪 Sair</a>
  </div>
</div>

<!-- Lista de Categorias -->
<div class="max-w-4xl mx-auto mt-6 bg-white shadow rounded-lg p-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">Lista de Categorias</h2>
    <a href="categorias.php?action=new" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">➕ Nova Categoria</a>
  </div>

  <table class="w-full border-collapse">
    <thead>
      <tr class="bg-gray-200">
        <th class="p-2 border">ID</th>
        <th class="p-2 border">Nome</th>
        <th class="p-2 border">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $res = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");
      while ($c = $res->fetch_assoc()):
      ?>
      <tr class="hover:bg-gray-50">
        <td class="p-2 border"><?php echo $c['id']; ?></td>
        <td class="p-2 border"><?php echo htmlspecialchars($c['name']); ?></td>
        <td class="p-2 border">
          <a href="categorias.php?action=edit&id=<?php echo $c['id']; ?>" class="text-blue-600 hover:underline">Editar</a> |
          <a href="categorias.php?action=delete&id=<?php echo $c['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Excluir esta categoria?')">Excluir</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php if ($action === 'new' || $action === 'edit'):
  $cat = ['id'=>0,'name'=>''];
  if ($action === 'edit' && $id) {
      $stmt = $mysqli->prepare("SELECT * FROM categories WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $cat = $stmt->get_result()->fetch_assoc();
  }
?>
<div class="max-w-lg mx-auto mt-6 bg-white shadow rounded-lg p-6">
  <h3 class="text-lg font-semibold mb-4"><?php echo $action==='new'?'Nova Categoria':'Editar Categoria'; ?></h3>
  <form method="post" action="categorias.php?action=save" class="space-y-4">
    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
    <div>
      <label class="block text-sm font-medium">Nome da Categoria</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required class="w-full border rounded p-2">
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
  </form>
</div>
<?php endif; ?>

</body>
</html>
