<?php
include "../config.php";
$page_title = 'Categorias';
include "header.php";
;

// Criar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $mysqli->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        header("Location: categorias.php?msg=Categoria criada com sucesso");
        exit;
    }
}

// Atualizar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($id > 0 && $name !== '') {
        $stmt = $mysqli->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: categorias.php?msg=Categoria atualizada");
        exit;
    }
}

// Deletar categoria
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: categorias.php?msg=Categoria removida");
        exit;
    }
}

// Buscar categorias
$result = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="p-6">
    <h2 class="text-2xl font-bold mb-6">Gerenciar Categorias</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            <?= htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de adicionar -->
    <form method="post" class="mb-6 flex gap-2">
        <input type="text" name="name" placeholder="Nova categoria" class="border rounded p-2 flex-1" required>
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Adicionar</button>
    </form>

    <!-- Listagem -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Nome</th>
                    <th class="p-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $result->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="p-3"><?= $cat['id']; ?></td>
                        <td class="p-3"><?= htmlspecialchars($cat['name'] ?? '-'); ?></td>
                        <td class="p-3 flex gap-2">
                            <!-- Botão Editar abre modal -->
                            <button onclick="openEdit(<?= $cat['id']; ?>,'<?= htmlspecialchars($cat['name'], ENT_QUOTES); ?>')" 
                                class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                Editar
                            </button>
                            <a href="?delete=<?= $cat['id']; ?>" 
                               onclick="return confirm('Deseja excluir esta categoria?')" 
                               class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                               Deletar
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Editar -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96 shadow">
        <h3 class="text-xl font-bold mb-4">Editar Categoria</h3>
        <form method="post">
            <input type="hidden" name="id" id="editId">
            <input type="text" name="name" id="editName" class="border rounded w-full p-2 mb-4" required>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEdit()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancelar</button>
                <button type="submit" name="edit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php include "footer.php"; ?>
