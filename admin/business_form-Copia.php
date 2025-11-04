<?php
include "../config.php";
include "header.php";

// --- Variáveis ---
$id = $_GET['id'] ?? null;
$editing = false;
$name = $description = $address = $phone = $whatsapp = $facebook = $instagram = $website = $image = "";
$category_id = null;

// --- Se edição ---
if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM businesses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $business = $res->fetch_assoc();
        $editing = true;
        $name = $business['name'];
        $description = $business['description'];
        $address = $business['address'];
        $phone = $business['phone'];
        $whatsapp = $business['whatsapp'];
        $facebook = $business['facebook'];
        $instagram = $business['instagram'];
        $website = $business['website'];
        $image = $business['image'];
        $category_id = $business['category_id'];
    }
    $stmt->close();
}

// --- Salvar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);

    // Upload de imagem
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $filename;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
        $image = $filename;
    }

    if ($editing) {$stmt = $mysqli->prepare("UPDATE businesses SET 
    name=?, description=?, category_id=?, image=?, slug=?, 
    facebook=?, instagram=?, whatsapp=?, paid_until=?, is_featured=? 
    WHERE id=?");

$stmt->bind_param("ssissssssii", 
    $name, $description, $category_id, $image, $slug, 
    $facebook, $instagram, $whatsapp, $paid_until, $is_featured, $id
);

        $stmt = $mysqli->prepare("UPDATE businesses 
  SET name=?, description=?, category_id=?, address=?, phone=?, email=?, website=?, facebook=?, instagram=?, image=?, is_featured=? 
  WHERE id=?");

$stmt->bind_param(
  "ssissssssiii",
  $name, $description, $category_id, $address, $phone, $email, $website, $facebook, $instagram, $image, $is_featured, $id
);

        $stmt->execute();
        $stmt->close();
        header("Location: index.php?msg=Negócio atualizado com sucesso");
        exit;
    } else {$is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $stmt = $mysqli->prepare("INSERT INTO businesses 
  (name, description, category_id, address, phone, email, website, facebook, instagram, image, is_featured) 
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
  "ssisssssssi",
  $name, $description, $category_id, $address, $phone, $email, $website, $facebook, $instagram, $image, $is_featured
);      
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?msg=Negócio adicionado com sucesso");
        exit;
    }
   $stmt = $mysqli->prepare("INSERT INTO businesses 
    (name, description, category_id, image, slug, facebook, instagram, whatsapp, paid_until, is_featured) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssissssssi", 
    $name, $description, $category_id, $image, $slug, 
    $facebook, $instagram, $whatsapp, $paid_until, $is_featured
);


}

// --- Categorias ---
$categories = $mysqli->query("SELECT id,name FROM categories ORDER BY name");
?>

<div class="p-6">
    <h2 class="text-2xl font-bold mb-6">
        <?= $editing ? "Editar Negócio" : "Adicionar Novo Negócio"; ?>
    </h2>

    <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4 bg-white p-6 rounded shadow">
        <div class="col-span-2">
            <label class="block font-semibold mb-1">Nome</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name); ?>" class="border rounded p-2 w-full" required>
        </div>

        <div class="col-span-2">
            <label class="block font-semibold mb-1">Descrição</label>
            <textarea name="description" rows="4" class="border rounded p-2 w-full"><?= htmlspecialchars($description); ?></textarea>
        </div>

        <div>
            <label class="block font-semibold mb-1">Endereço</label>
            <input type="text" name="address" value="<?= htmlspecialchars($address); ?>" class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Telefone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone); ?>" class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">WhatsApp</label>
            <input type="text" name="whatsapp" value="<?= htmlspecialchars($whatsapp); ?>" class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Facebook</label>
            <input type="text" name="facebook" value="<?= htmlspecialchars($facebook); ?>" class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Instagram</label>
            <input type="text" name="instagram" value="<?= htmlspecialchars($instagram); ?>" class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Website</label>
            <input type="text" name="website" value="<?= htmlspecialchars($website); ?>" class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block font-semibold mb-1">Categoria</label>
            <select name="category_id" class="border rounded p-2 w-full" required>
                <option value="">-- selecione --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id']; ?>" <?= $category_id == $cat['id'] ? "selected" : ""; ?>>
                        <?= htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Imagem</label>
            <input type="file" name="image" class="border rounded p-2 w-full">
            <?php if ($image): ?>
                <img src="../uploads/<?= htmlspecialchars($image); ?>" alt="Imagem" class="mt-2 w-32 h-32 object-cover rounded">
            <?php endif; ?>
        </div>

        <div class="col-span-2 flex justify-end gap-2 mt-4">
            <a href="index.php" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <?= $editing ? "Salvar Alterações" : "Adicionar Negócio"; ?>
            </button>
        </div>
     <div class="mb-3">
  <label for="is_featured" class="form-label">Destacar empresa?</label>
  <input type="checkbox" id="is_featured" name="is_featured" value="1"
    <?php if (!empty($business['is_featured'])) echo 'checked'; ?>>
</div>


     
                      
</div>
 
    </form>
</div>

<?php include "footer.php"; ?>
