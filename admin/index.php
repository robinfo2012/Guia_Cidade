<?php
session_start();
require_once("../config.php");

// Verifica se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Busca as empresas com categorias
$result = $mysqli->query("
    SELECT b.*, c.name AS category_name 
    FROM businesses b
    LEFT JOIN categories c ON b.category_id = c.id
    ORDER BY b.id DESC
");

// parâmetros de paginação
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// busca
$search = trim($_GET['search'] ?? '');

// contar total de registros (com ou sem busca)
if ($search !== '') {
    $like = "%{$search}%";
    $stmt_count = $mysqli->prepare("SELECT COUNT(*) FROM businesses WHERE name LIKE ? OR description LIKE ?");
    $stmt_count->bind_param("ss", $like, $like);
} else {
    $stmt_count = $mysqli->prepare("SELECT COUNT(*) FROM businesses");
}
$stmt_count->execute();
$stmt_count->bind_result($total_rows);
$stmt_count->fetch();
$stmt_count->close();

$total_rows = (int)$total_rows;
$total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

// buscar resultados da página atual
if ($search !== '') {
    $stmt = $mysqli->prepare("
        SELECT b.*, c.name AS category_name
        FROM businesses b
        LEFT JOIN categories c ON b.category_id = c.id
        WHERE b.name LIKE ? OR b.description LIKE ?
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $mysqli->prepare("
        SELECT b.*, c.name AS category_name
        FROM businesses b
        LEFT JOIN categories c ON b.category_id = c.id
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>🏠 Negócios - Painel Admin</title>
    </head>
<body>
    <?php include("header.php"); ?>

    <div class="container">
         <!-- formulário de busca -->
           <form method="get" class="max-w-2xl mx-auto flex bg-white rounded-lg overflow-hidden shadow-lg">
      <input type="text" name="search" placeholder="Buscar por nome ou descrição" 
             value="<?= htmlspecialchars($search) ?>" 
             class="flex-1 border rounded-l px-3 py-2 focus:outline-none">
      <button type="submit" class="btn mb-6 flex gap-2 bg-yellow-500 px-4 py-2 rounded text-white hover:bg-yellow-500">Buscar</button>
    </form>
 <!-- Fim da busca-->
        <h1>Painel do Administrador</h1>
        <a class="btn btn-green" href="business_form.php">+ Adicionar Empresa</a>
        <!--<a class="btn" href="categorias.php">Gerenciar Categorias</a>
        <a class="btn" href="usuarios.php">Gerenciar Usuários</a>
        <a class="btn" href="settings.php">Configurações</a>-->
 

        <table class="tabela">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Telefone</th>
                    <th>Website</th>
                    <th>Facebook</th>
                    <th>Instagram</th>
                    <th>Imagem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($row['website'])): ?>
                                <a href="<?php echo htmlspecialchars($row['website']); ?>" target="_blank">Visitar</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank">Facebook</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['instagram'])): ?>
                                <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank">Instagram</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="Logo" style="border: 1px solid #10b981; width:60px;height:40px;border-radius:4px;">
                            <?php else: ?>
                                Sem imagem
                            <?php endif; ?>
                        </td>

                        
                        <td>
                            <a class="btn-small" href="business_form.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a class="btn-small danger" href="delete_business.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                            
                        </td>
                        <th class="px-4 py-2">Destaque</th>
                        <td class="px-4 py-2 text-center">
                        <?php echo $row['is_featured'] ? "⭐ Sim" : "—"; ?>
                        </td>


                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
         <!-- paginação -->
    <div class="mt-6 flex justify-center items-center gap-2">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Anterior</a>
      <?php endif; ?>

      <?php
      // mostra um range reduzido de páginas para não poluir a interface
      $start = max(1, $page - 3);
      $end = min($total_pages, $page + 3);
      for ($i = $start; $i <= $end; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
           class="px-3 py-1 rounded <?= $i === $page ? 'bg-yellow-500 text-white' : 'bg-white border hover:bg-gray-50' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Próximo</a>
      <?php endif; ?>
    </div>

    <div class="mt-4 text-center text-xs text-gray-500">
      Exibindo página <?= $page ?> de <?= $total_pages ?> — <?= $total_rows ?> registros
    </div>
    </div>
</body>
</html>
