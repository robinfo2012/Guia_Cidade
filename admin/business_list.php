<?php

require_once("../config.php");



// Se for exclusão
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Deleta imagem se existir
    $stmt = $mysqli->prepare("SELECT image FROM businesses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && !empty($result['image']) && file_exists("../".$result['image'])) {
        unlink("../".$result['image']);
    }

    // Deleta o registro
    $stmt = $mysqli->prepare("DELETE FROM businesses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: business_list.php?success=1");
    exit;
}

// Consulta todos os negócios
$result = $mysqli->query("SELECT b.*, c.name AS category_name 
                          FROM businesses b 
                          LEFT JOIN categories c ON b.category_id = c.id 
                          ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Lista de Negócios</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Lista de Negócios</h1>

    <a href="business_form.php" class="mb-4 inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
      + Novo Negócio
    </a>

    <?php if (isset($_GET['success'])): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        Ação realizada com sucesso!
      </div>
    <?php endif; ?>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="min-w-full text-sm text-left border-collapse">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="p-3">Imagem</th>
            <th class="p-3">Nome</th>
            <th class="p-3">Categoria</th>
            <th class="p-3">Telefone</th>
            <th class="p-3">Email</th>
            <th class="p-3">Destaque</th>
            <th class="p-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($b = $result->fetch_assoc()): ?>
          <tr class="border-t hover:bg-gray-50">
            <td class="p-3">
              <?php if (!empty($b['image'])): ?>
                <img src="../<?php echo $b['image']; ?>" class="h-12 rounded shadow">
              <?php else: ?>
                <span class="text-gray-400 italic">Sem imagem</span>
              <?php endif; ?>
            </td>
            <td class="p-3 font-semibold text-blue-600"><?php echo htmlspecialchars($b['name']); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($b['category_name'] ?? '-'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($b['phone']); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($b['email']); ?></td>
            <td class="p-3">
              <?php if ($b['is_featured']): ?>
                <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Sim</span>
              <?php else: ?>
                <span class="text-gray-500 text-xs">Não</span>
              <?php endif; ?>
            </td>
            <td class="p-3 text-right space-x-2">
              <a href="business_form.php?id=<?php echo $b['id']; ?>" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Editar</a>
              <a href="business_list.php?delete=<?php echo $b['id']; ?>" 
                 onclick="return confirm('Tem certeza que deseja excluir este negócio?')" 
                 class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Excluir</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
