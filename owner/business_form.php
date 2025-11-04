<?php
require_once __DIR__ . "/../config_owner.php";
include "header.php"; // topo com menu

$owner_id = $_SESSION['owner_id'];
$business = null;

// Carregar empresa do dono
$stmt = $conn->prepare("SELECT * FROM businesses WHERE owner_id = ? LIMIT 1");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$business = $result->fetch_assoc();

// Salvar alterações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    // Upload de imagem
    $image = $business['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
    }

    if ($business) {
        // Update
        $stmt = $conn->prepare("UPDATE businesses SET name=?, description=?, address=?, phone=?, category_id=?, image=? WHERE id=? AND owner_id=?");
        $stmt->bind_param("ssssiisi", $name, $description, $address, $phone, $category_id, $image, $business['id'], $owner_id);
        $stmt->execute();
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO businesses (name, description, address, phone, category_id, image, owner_id, slug) VALUES (?,?,?,?,?,?,?,?)");
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
        $stmt->bind_param("ssssisis", $name, $description, $address, $phone, $category_id, $image, $owner_id, $slug);
        $stmt->execute();
    }

    header("Location: business_list.php");
    exit;
}
?>

<h2>✏️ <?= $business ? "Editar" : "Cadastrar" ?> Empresa</h2>

<form method="POST" enctype="multipart/form-data" style="max-width:800px;margin:auto;background:#fff;padding:20px;border-radius:8px;">
  <label>Nome:</label>
  <input type="text" name="name" value="<?= htmlspecialchars($business['name'] ?? '') ?>" required style="width:100%;padding:8px;margin-bottom:10px;">

  <label>Descrição:</label>
  <textarea name="description" rows="4" style="width:100%;padding:8px;margin-bottom:10px;"><?= htmlspecialchars($business['description'] ?? '') ?></textarea>

  <label>Endereço:</label>
  <input type="text" name="address" value="<?= htmlspecialchars($business['address'] ?? '') ?>" style="width:100%;padding:8px;margin-bottom:10px;">

  <label>Telefone:</label>
  <input type="text" name="phone" value="<?= htmlspecialchars($business['phone'] ?? '') ?>" style="width:100%;padding:8px;margin-bottom:10px;">

  <label>Categoria:</label>
  <select name="category_id" style="width:100%;padding:8px;margin-bottom:10px;">
    <option value="">Selecione</option>
    <?php
    $cats = $conn->query("SELECT id, name FROM categories ORDER BY name");
    while ($c = $cats->fetch_assoc()):
    ?>
      <option value="<?= $c['id'] ?>" <?= ($business && $business['category_id'] == $c['id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['name']) ?>
      </option>
    <?php endwhile; ?>
  </select>

  <label>Imagem:</label><br>
  <?php if (!empty($business['image'])): ?>
    <img src="../uploads/<?= htmlspecialchars($business['image']) ?>" width="120" style="margin-bottom:10px;"><br>
  <?php endif; ?>
  <input type="file" name="image" style="margin-bottom:10px;">

  <button type="submit" style="background:#1e3a8a;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">
    Salvar
  </button>
</form>

<?php include "footer.php"; ?>