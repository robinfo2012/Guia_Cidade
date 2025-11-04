<?php
$page_title = 'Home'; // ou 'Usuários', 'Categorias'
include "header.php";

include "../config.php";


// ======================
// Função para upload de imagem
// ======================
function uploadImage($file) {
    if (!empty($file['name'])) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . "_" . basename($file['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return "uploads/" . $filename; // caminho salvo no banco
        }
    }
    return null;
}

// ======================
// Adicionar Negócio
// ======================
if (isset($_POST['add'])) {
    $name        = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $paid_until  = $_POST['paid_until'] ?? null;
    $facebook    = $_POST['facebook'] ?? '';
    $instagram   = $_POST['instagram'] ?? '';
    $whatsapp    = $_POST['whatsapp'] ?? '';
    $website     = $_POST['website'] ?? '';

    // Verifica categoria
    if ($category_id) {
        $check = $mysqli->query("SELECT id FROM categories WHERE id='$category_id'");
        if ($check->num_rows == 0) $category_id = null;
    }

    $image = uploadImage($_FILES['image']);

    $stmt = $mysqli->prepare("INSERT INTO businesses 
        (name, description, category_id, image, paid_until, facebook, instagram, whatsapp, website) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssss", $name, $description, $category_id, $image, $paid_until, $facebook, $instagram, $whatsapp, $website);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}

// ======================
// Editar Negócio
// ======================
if (isset($_POST['edit'])) {
    $id          = (int)$_POST['id'];
    $name        = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $paid_until  = $_POST['paid_until'] ?? null;
    $facebook    = $_POST['facebook'] ?? '';
    $instagram   = $_POST['instagram'] ?? '';
    $whatsapp    = $_POST['whatsapp'] ?? '';
    $website     = $_POST['website'] ?? '';

    if ($category_id) {
        $check = $mysqli->query("SELECT id FROM categories WHERE id='$category_id'");
        if ($check->num_rows == 0) $category_id = null;
    }

    $image = uploadImage($_FILES['image']);
    if ($image) {
        $stmt = $mysqli->prepare("UPDATE businesses 
            SET name=?, description=?, category_id=?, image=?, paid_until=?, facebook=?, instagram=?, whatsapp=?, website=? 
            WHERE id=?");
        $stmt->bind_param("ssissssssi", $name, $description, $category_id, $image, $paid_until, $facebook, $instagram, $whatsapp, $website, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE businesses 
            SET name=?, description=?, category_id=?, paid_until=?, facebook=?, instagram=?, whatsapp=?, website=? 
            WHERE id=?");
        $stmt->bind_param("ssisssssi", $name, $description, $category_id, $paid_until, $facebook, $instagram, $whatsapp, $website, $id);
    }

    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}

// ======================
// Excluir Negócio
// ======================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $mysqli->query("DELETE FROM businesses WHERE id=$id");
    header("Location: index.php");
    exit;
}

// ======================
// Listagem
// ======================
$res = $mysqli->query("SELECT b.*, c.name AS category_name 
    FROM businesses b 
    LEFT JOIN categories c ON b.category_id = c.id 
    ORDER BY b.id DESC");

$cats = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Painel Administrativo - Negócios</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-6xl mx-auto p-6">
  <!--<h1 class="text-2xl font-bold text-blue-600 mb-6">📋 Gerenciar Negócios</h1>
 <div class="flex space-x-4">
 <a href="index.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">🏠 Negócios</a>
    <a href="usuarios.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">👤 Usuários</a>
    <a href="categorias.php" class="px-4 py-2 bg-blue-600 text-white rounded">📂 Categorias</a>
    <a href="settings.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">⚙ Configurações</a>
    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded">🚪 Sair</a>-->
</div>




  <!-- Formulário de Cadastro -->
  <div class="bg-white p-6 rounded-xl shadow mb-10">
    <h2 class="text-xl font-semibold mb-4">Adicionar Novo Negócio</h2>
    <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="text" name="name" placeholder="Nome" class="border p-2 rounded" required>
      <input type="text" name="website" placeholder="Website" class="border p-2 rounded">
      <textarea name="description" placeholder="Descrição" class="border p-2 rounded md:col-span-2"></textarea>

      <select name="category_id" class="border p-2 rounded">
        <option value="">-- Categoria --</option>
        <?php while($c=$cats->fetch_assoc()): ?>
          <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
        <?php endwhile; ?>
      </select>

      <input type="date" name="paid_until" class="border p-2 rounded">

      <input type="text" name="facebook" placeholder="Facebook URL" class="border p-2 rounded">
      <input type="text" name="instagram" placeholder="Instagram URL" class="border p-2 rounded">
      <input type="text" name="whatsapp" placeholder="WhatsApp (com DDD)" class="border p-2 rounded">

      <input type="file" name="image" class="border p-2 rounded md:col-span-2">

      <button type="submit" name="add" class="bg-blue-600 text-white py-2 rounded hover:bg-blue-700 md:col-span-2">Salvar</button>
    </form>
  </div>

  <!-- Lista de Negócios -->
  <div class="bg-white p-6 rounded-xl shadow">
    <h2 class="text-xl font-semibold mb-4">Negócios Cadastrados</h2>
    <table class="w-full border-collapse">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="p-2">Nome</th>
          <th class="p-2">Categoria</th>
          <th class="p-2">Plano até</th>
          <th class="p-2">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while($b=$res->fetch_assoc()): ?>
        <tr class="border-b">
          <td class="p-2"><?= htmlspecialchars($b['name']); ?></td>
          <td class="p-2"><?= htmlspecialchars($b['category_name'] ?? 'Sem categoria'); ?></td>
          <td class="p-2"><?= $b['paid_until'] ? date("d/m/Y", strtotime($b['paid_until'])) : '—'; ?></td>
          <td class="p-2 space-x-2">
            <a href="edit.php?id=<?= $b['id']; ?>" class="bg-yellow-500 text-white px-3 py-1 rounded">Editar</a>
            <a href="?delete=<?= $b['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')" class="bg-red-600 text-white px-3 py-1 rounded">Excluir</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
