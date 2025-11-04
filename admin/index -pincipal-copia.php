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
    </div>
</body>
</html>
