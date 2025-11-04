<?php
require_once __DIR__ . "/../config_owner.php";
include "header.php"; // topo com menu

$owner_id = $_SESSION['owner_id'];

// Busca empresa do dono
$stmt = $conn->prepare("SELECT b.*, c.name AS category_name 
                        FROM businesses b
                        LEFT JOIN categories c ON b.category_id = c.id
                        WHERE b.owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$business = $result->fetch_assoc();
?>

<h2>🏢 Minha Empresa</h2>

<?php if ($business): ?>
  <table border="1" cellpadding="8" cellspacing="0" style="background:#fff;border-collapse:collapse;width:100%;max-width:800px;margin:auto;">
    <tr><th>Nome</th><td><?= htmlspecialchars($business['name']) ?></td></tr>
    <tr><th>Categoria</th><td><?= htmlspecialchars($business['category_name'] ?? "Não definida") ?></td></tr>
    <tr><th>Descrição</th><td><?= nl2br(htmlspecialchars($business['description'])) ?></td></tr>
    <tr><th>Endereço</th><td><?= htmlspecialchars($business['address']) ?></td></tr>
    <tr><th>Telefone</th><td><?= htmlspecialchars($business['phone']) ?></td></tr>
    <tr><th>Imagem</th>
        <td>
        <?php if ($business['image']): ?>
            <img src="../uploads/<?= htmlspecialchars($business['image']) ?>" width="120">
        <?php else: ?>
            Nenhuma imagem cadastrada
        <?php endif; ?>
        </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center;">
        <a href="business_form.php" style="background:#1e3a8a;color:white;padding:8px 15px;border-radius:6px;text-decoration:none;">✏️ Editar Empresa</a>
      </td>
    </tr>
  </table>
<?php else: ?>
  <p style="text-align:center;">Você ainda não cadastrou sua empresa.</p>
  <div style="text-align:center;">
    <a href="business_form.php" style="background:#16a34a;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;">➕ Cadastrar Empresa</a>
  </div>
<?php endif; ?>

<?php include "footer.php"; ?>