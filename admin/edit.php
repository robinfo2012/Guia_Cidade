<?php
include "../config.php";

// ======================
// Verifica ID
// ======================
if (!isset($_GET['id'])) {
    die("ID inválido");
}
$id = (int)$_GET['id'];

// ======================
// Função Upload de Imagem
// ======================
function uploadImage($file) {
    if (!empty($file['name'])) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . "_" . basename($file['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return "uploads/" . $filename;
        }
    }
    return null;
}

// ======================
// Atualizar Negócio
// ======================
if (isset($_POST['update'])) {
    $name        = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $paid_until  = $_POST['paid_until'] ?? null;
    $facebook    = $_POST['facebook'] ?? '';
    $instagram   = $_POST['instagram'] ?? '';
    $whatsapp    = $_POST['whatsapp'] ?? '';
    $website     = $_POST['website'] ?? '';

    // Valida categoria
    if ($category_id) {
        $check = $mysqli->query("SELECT id FROM categories WHERE id='$category_id'");
        if ($check->num_rows == 0) $category_id = null;
    }

    // Upload de imagem
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
// Pega dados atuais
// ======================
$stmt = $mysqli->prepare("SELECT * FROM businesses WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$business = $result->fetch_assoc();
$stmt->close();

if (!$business) {
    die("Negócio não encontrado.");
}

// Categorias
$cats = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Editar Negócio</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold text-blue-600 mb-6">✏️ Editar Negócio</h1>

  <form method="post" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow grid grid-cols-1 md:grid-cols-2 gap-4">
    <input type="text" name="name" value="<?= htmlspecialchars($business['name']); ?>" placeholder="Nome" class="border p-2 rounded" required>
    <input type="text" name="website" value="<?= htmlspecialchars($business['website']); ?>" placeholder="Website" class="border p-2 rounded">

    <textarea name="description" placeholder="Descrição" class="border p-2 rounded md:col-span-2"><?= htmlspecialchars($business['description']); ?></textarea>

    <select name="category_id" class="border p-2 rounded">
      <option value="">-- Categoria --</option>
      <?php while($c=$cats->fetch_assoc()): ?>
        <option value="<?= $c['id']; ?>" <?= $business['category_id'] == $c['id'] ? 'selected' : ''; ?>>
          <?= htmlspecialchars($c['name']); ?>
        </option>
      <?php endwhile; ?>
    </select>

    <input type="date" name="paid_until" value="<?= $business['paid_until']; ?>" class="border p-2 rounded">

    <input type="text" name="facebook" value="<?= htmlspecialchars($business['facebook']); ?>" placeholder="Facebook URL" class="border p-2 rounded">
    <input type="text" name="instagram" value="<?= htmlspecialchars($business['instagram']); ?>" placeholder="Instagram URL" class="border p-2 rounded">
    <input type="text" name="whatsapp" value="<?= htmlspecialchars($business['whatsapp']); ?>" placeholder="WhatsApp (com DDD)" class="border p-2 rounded">

    <!-- Imagem atual -->
    <div class="md:col-span-2">
      <?php if(!empty($business['image'])): ?>
        <p class="mb-2">Imagem atual:</p>
        <img src="../<?= $business['image']; ?>" alt="Imagem atual" class="h-32 rounded mb-2">
      <?php endif; ?>
      <input type="file" name="image" class="border p-2 rounded w-full">
    </div>

    <button type="submit" name="update" class="bg-blue-600 text-white py-2 rounded hover:bg-blue-700 md:col-span-2">Atualizar</button>
  </form>

  <div class="mt-6">
    <a href="index.php" class="text-blue-600 hover:underline">⬅ Voltar</a>
  </div>
</div>

</body>
</html>
