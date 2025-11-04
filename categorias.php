<?php
include "config.php";

if (!isset($_GET['cat'])) {
    die("Categoria inválida.");
}
$cat_id = (int)$_GET['cat'];

// Pega categoria
$stmt = $mysqli->prepare("SELECT * FROM categories WHERE id=?");
$stmt->bind_param("i", $cat_id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cat) {
    die("Categoria não encontrada.");
}

// Pega negócios dessa categoria
$stmt = $mysqli->prepare("SELECT * FROM businesses WHERE category_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $cat_id);
$stmt->execute();
$businesses = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($cat['name']); ?> - Guia da Cidade</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">🌆 Guia da Cidade</h1>
    <a href="index.php" class="text-blue-600 hover:underline">⬅ Voltar</a>
  </div>
</header>

<section class="max-w-7xl mx-auto px-6 py-12">
  <h2 class="text-3xl font-bold text-gray-700 mb-8"><?= htmlspecialchars($cat['name']); ?></h2>

  <?php if ($businesses->num_rows > 0): ?>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php while($b = $businesses->fetch_assoc()): ?>
        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
          <?php if(!empty($b['image'])): ?>
            <img src="<?= htmlspecialchars($b['image']); ?>" alt="<?= htmlspecialchars($b['name']); ?>" class="h-32 w-full object-cover">
          <?php else: ?>
            <div class="h-32 bg-gradient-to-r from-blue-100 to-blue-50"></div>
          <?php endif; ?>
          <div class="p-4 flex-1 flex flex-col">
            <h4 class="text-lg font-semibold text-blue-600 mb-2"><?= htmlspecialchars($b['name']); ?></h4>
            <p class="text-sm text-gray-600 flex-1"><?= substr(strip_tags($b['description']),0,80).'...'; ?></p>
            <a href="detalhes.php?id=<?= $b['id']; ?>" class="mt-4 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">Ver Mais</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="text-gray-600">Nenhum negócio encontrado nesta categoria.</p>
  <?php endif; ?>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-300 py-6 mt-12 text-center">
  <p>&copy; <?= date('Y'); ?> Guia da Cidade - Todos os direitos reservados.</p>
</footer>

</body>
</html>
