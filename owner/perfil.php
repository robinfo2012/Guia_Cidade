<?php
require_once __DIR__ . "/../config_owner.php";
if (session_status() == PHP_SESSION_NONE) session_start();

if (empty($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit;
}

$business_id = $_SESSION['business_id'];
$business = $mysqli->query("SELECT * FROM businesses WHERE id=$business_id")->fetch_assoc();
$msg = '';

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = $_POST['description'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $whatsapp = $_POST['whatsapp'];
    $website = $_POST['website'];
    $facebook = $_POST['facebook'];
    $instagram = $_POST['instagram'];

    // Upload de imagem
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed)) {
            $new_name = 'upload/'.uniqid().'_'.$business_id.'.'.$ext;
            if (!is_dir('upload')) mkdir('upload', 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $new_name);
            $image_sql = ", image='".$mysqli->real_escape_string($new_name)."'";
        } else {
            $msg = "Formato de imagem inválido.";
        }
    } else {
        $image_sql = "";
    }

    // Atualizar banco
    $stmt = $mysqli->prepare("UPDATE businesses SET name=?, description=?, address=?, phone=?, whatsapp=?, website=?, facebook=?, instagram=? $image_sql WHERE id=?");
    $stmt->bind_param("ssssssssi", $name, $description, $address, $phone, $whatsapp, $website, $facebook, $instagram, $business_id);
    if($stmt->execute()){
        $msg = "Perfil atualizado com sucesso!";
        $business = $mysqli->query("SELECT * FROM businesses WHERE id=$business_id")->fetch_assoc();
    } else {
        $msg = "Erro ao atualizar perfil.";
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Editar Perfil</title>
<script src="https://cdn.tailwindcss.com"></script>
</head><?php include "header.php"; ?>

<body class="bg-gray-100">
<div class="max-w-3xl mx-auto mt-6 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">Editar Perfil</h1>
    <?php if($msg): ?>
    <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="font-semibold">Nome da Empresa</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($business['name']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">Descrição</label>
            <textarea name="description" class="w-full border rounded p-2"><?= htmlspecialchars($business['description']) ?></textarea>
        </div>
        <div>
            <label class="font-semibold">Endereço</label>
            <input type="text" name="address" value="<?= htmlspecialchars($business['address']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">Telefone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($business['phone']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">WhatsApp</label>
            <input type="text" name="whatsapp" value="<?= htmlspecialchars($business['whatsapp']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">Site</label>
            <input type="text" name="website" value="<?= htmlspecialchars($business['website']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">Facebook</label>
            <input type="text" name="facebook" value="<?= htmlspecialchars($business['facebook']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">Instagram</label>
            <input type="text" name="instagram" value="<?= htmlspecialchars($business['instagram']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="font-semibold">Imagem / Logo</label>
            <input type="file" name="image" class="w-full border rounded p-2">
            <?php if($business['image']): ?>
                <img src="<?= htmlspecialchars($business['image']) ?>" class="mt-2 w-32 h-32 object-cover rounded">
            <?php endif; ?>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Salvar Alterações</button>
        <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Voltar</a>
    </form>
</div>
</body>
</html>
