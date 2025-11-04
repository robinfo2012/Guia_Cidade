<?php
include "../config.php";
include "header.php";



// Função para gerar slug único
function generateSlug($mysqli, $name, $id = null) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
    $slug = trim($slug, '-');

    $unique = $slug;
    $i = 1;
    while (true) {
        if ($id) {
            $check = $mysqli->prepare("SELECT id FROM businesses WHERE slug=? AND id!=?");
            $check->bind_param("si", $unique, $id);
        } else {
            $check = $mysqli->prepare("SELECT id FROM businesses WHERE slug=?");
            $check->bind_param("s", $unique);
        }
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows == 0) break;
        $unique = $slug . '-' . $i++;
    }

    return $unique;
}

// Inicializa variáveis
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$name = $description = $email = $phone = $address = $facebook = $instagram = $whatsapp = $website = $planoo = "";
$image = "";
$category_id = 0;
$is_featured = 0;

// Se edição, busca os dados
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM businesses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name        = $row['name'];
        $description = $row['description'];
        $email       = $row['email'];
        $phone       = $row['phone'];
        $address     = $row['address'];
        $facebook    = $row['facebook'];
        $instagram   = $row['instagram'];
        $whatsapp    = $row['whatsapp'];
        $website     = $row['website'];
        $category_id = $row['category_id'];
        $is_featured = $row['is_featured'];
        $image       = $row['image'];
        $plano     = $row['plano'];
    }
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $email       = $_POST['email'] ?? '';
    $phone       = $_POST['phone'] ?? '';
    $address     = $_POST['address'] ?? '';
    $facebook    = $_POST['facebook'] ?? '';
    $instagram   = $_POST['instagram'] ?? '';
    $whatsapp    = $_POST['whatsapp'] ?? '';
    $website     = $_POST['website'] ?? '';
    $category_id = $_POST['category_id'] ?? 0;
 /*   $plano = $_POST['plano'] ;    */
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Gera slug único
    $slug = generateSlug($mysqli, $name, $id);

    // Upload da imagem (se enviada)
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . "." . $ext;
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = "uploads/" . $fileName;
        }
    }

    if ($id > 0) {
        // Update
        $stmt = $mysqli->prepare("UPDATE businesses SET name=?, slug=?, description=?, email=?, phone=?, address=?, facebook=?, instagram=?, whatsapp=?, website=?, category_id=?, is_featured=?, image=? WHERE id=?");
        $stmt->bind_param("ssssssssssissi", $name, $slug, $description, $email, $phone, $address, $facebook, $instagram, $whatsapp, $website, $category_id, $is_featured, $image, $id);
        $stmt->execute();
    } else {
        // Insert
        $stmt = $mysqli->prepare("INSERT INTO businesses (name, slug, description, email, phone, address, facebook, instagram, whatsapp, website, category_id, is_featured, image, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->bind_param("ssssssssssiss", $name, $slug, $description, $email, $phone, $address, $facebook, $instagram, $whatsapp, $website, $category_id, $is_featured, $image);
        $stmt->execute();
    }

    header("Location: index.php?msg=salvo");
    exit;
}

// Carrega categorias
$cats = $mysqli->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Empresa</title>
  <link rel="stylesheet" href="https://cdn.tailwindcss.com">
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md mt-10">
    <h2 class="text-2xl font-bold mb-6"><?= $id > 0 ? "Editar Empresa" : "Cadastrar Nova Empresa" ?></h2>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-semibold">Nome</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block font-semibold">Descrição</label>
        <textarea name="description" class="w-full border p-2 rounded"><?= htmlspecialchars($description) ?></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold">E-mail</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border p-2 rounded">
        </div>
        <div>
          <label class="block font-semibold">Telefone</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" class="w-full border p-2 rounded">
        </div>
      </div>
      <div>
        <label class="block font-semibold">Endereço</label>
        <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" class="w-full border p-2 rounded">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold">Facebook</label>
          <input type="text" name="facebook" value="<?= htmlspecialchars($facebook) ?>" class="w-full border p-2 rounded">
        </div>
        <div>
          <label class="block font-semibold">Instagram</label>
          <input type="text" name="instagram" value="<?= htmlspecialchars($instagram) ?>" class="w-full border p-2 rounded">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold">WhatsApp</label>
          <input type="text" name="whatsapp" value="<?= htmlspecialchars($whatsapp) ?>" class="w-full border p-2 rounded">
        </div>
        <div>
          <label class="block font-semibold">Website</label>
          <input type="text" name="website" value="<?= htmlspecialchars($website) ?>" class="w-full border p-2 rounded">
        </div>
      </div>
      <div>
           <!------------>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <!-- Categoria -->
  <div>
   
    <label class="block font-semibold">Categoria</label>
         <select name="category_id" class="w-full border p-2 rounded">
          <option value="">-- Selecione --</option>
          <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
  </div>


  <div>
    <label class="block font-semibold">Plano</label>
     <!-- Plano  <select name="plano" class="w-full border p-2 rounded" required>
      <option value="gratuito" <?= htmlspecialchars($c['plano']) ?>>Gratuito</option>
      <option value="basico" <?= ($plano == 'basico') ? 'selected' : '' ?>>Básico</option>
      <option value="premium" <?= ($plano == 'premium') ? 'selected' : '' ?>>Premium</option>
    </select>-->
  </div>
</div>
        <!---------------->
        <label class="block font-semibold">Imagem</label>
        <input type="file" name="image" class="w-full border p-2 rounded">
        <?php if (!empty($image)): ?>
          <img src="../<?= $image ?>" alt="Imagem atual" class="w-32 mt-2 rounded shadow">
        <?php endif; ?>
      </div>
      <div class="flex items-center space-x-2">
        <input type="checkbox" name="is_featured" <?= $is_featured ? 'checked' : '' ?>>
        <span>Empresa em Destaque</span>
      </div>
      <div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Salvar</button>
        <a href="index.php" class="ml-2 text-gray-600">Cancelar</a>
      </div>
    </form>
  </div>
</body>
</html>
