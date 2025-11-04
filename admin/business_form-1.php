<?php
include "../config.php";
include "header.php";

$id = $_GET['id'] ?? null;
$business = null;

if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM businesses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $business = $stmt->get_result()->fetch_assoc();
}

// Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $phone       = $_POST['phone'] ?? '';
    $email       = $_POST['email'] ?? '';
    $address     = $_POST['address'] ?? '';
    $website     = $_POST['website'] ?? '';
    $facebook    = $_POST['facebook'] ?? '';
    $instagram   = $_POST['instagram'] ?? '';
    $whatsapp    = $_POST['whatsapp'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Upload de imagem
    $imagePath = $business['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $target   = "../uploads/" . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = "uploads/" . $fileName;
        }
    }

    if ($id) {
        // Atualizar
        $stmt = $mysqli->prepare("UPDATE businesses 
            SET name=?, description=?, category_id=?, phone=?, email=?, address=?, website=?, facebook=?, instagram=?, whatsapp=?, image=?, is_featured=? 
            WHERE id=?");
        $stmt->bind_param("ssissssssssii", 
            $name, $description, $category_id, $phone, $email, $address, $website, $facebook, $instagram, $whatsapp, $imagePath, $is_featured, $id
        );
        $stmt->execute();
    } else {
        // Inserir novo
        $stmt = $mysqli->prepare("INSERT INTO businesses 
            (name, description, category_id, phone, email, address, website, facebook, instagram, whatsapp, image, is_featured, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssissssssssi", 
            $name, $description, $category_id, $phone, $email, $address, $website, $facebook, $instagram, $whatsapp, $imagePath, $is_featured
        );
        $stmt->execute();
    }

    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Negócio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg mt-10">
        <h2 class="text-2xl font-bold mb-6"><?php echo $id ? "Editar Negócio" : "Novo Negócio"; ?></h2>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">

            <input type="text" name="name" placeholder="Nome do Negócio" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['name'] ?? ''); ?>" required>

            <textarea name="description" placeholder="Descrição" class="w-full border p-3 rounded" rows="4"><?php echo htmlspecialchars($business['description'] ?? ''); ?></textarea>

            <select name="category_id" class="w-full border p-3 rounded" required>
                <option value="">Selecione a Categoria</option>
                <?php
                $cats = $mysqli->query("SELECT * FROM categories ORDER BY name");
                while ($c = $cats->fetch_assoc()):
                ?>
                    <option value="<?php echo $c['id']; ?>" <?php if (($business['category_id'] ?? '') == $c['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="text" name="phone" placeholder="Telefone" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['phone'] ?? ''); ?>">

            <input type="email" name="email" placeholder="Email" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['email'] ?? ''); ?>">

            <input type="text" name="address" placeholder="Endereço" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['address'] ?? ''); ?>">

            <input type="url" name="website" placeholder="Website" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['website'] ?? ''); ?>">

            <input type="url" name="facebook" placeholder="Facebook" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['facebook'] ?? ''); ?>">

            <input type="url" name="instagram" placeholder="Instagram" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['instagram'] ?? ''); ?>">

            <input type="text" name="whatsapp" placeholder="WhatsApp" class="w-full border p-3 rounded" value="<?php echo htmlspecialchars($business['whatsapp'] ?? ''); ?>">

            <div>
                <label class="block mb-2 font-semibold">Imagem do Negócio:</label>
                <input type="file" name="image" class="w-full border p-2 rounded">
                <?php if (!empty($business['image'])): ?>
                    <img src="../<?php echo $business['image']; ?>" alt="Imagem atual" class="mt-2 h-24 rounded shadow">
                <?php endif; ?>
            </div>

            <label class="flex items-center space-x-2">
                <input type="checkbox" name="is_featured" <?php if (!empty($business['is_featured'])) echo "checked"; ?>>
                <span>Destacar no slideshow</span>
            </label>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                <?php echo $id ? "Salvar Alterações" : "Cadastrar"; ?>
            </button>
        </form>
    </div>
</body>
</html>
