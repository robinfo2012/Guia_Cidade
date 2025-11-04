<?php
require_once("config.php");
$cat_id = (int)($_GET['id'] ?? 0);

// Busca categoria
$cat = $mysqli->query("SELECT name FROM categories WHERE id=$cat_id")->fetch_assoc();

// Busca negócios da categoria
$negocios = $mysqli->query("SELECT * FROM businesses WHERE category_id=$cat_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
 <script src="https://cdn.tailwindcss.com"></script>
  <meta name="description" content="Descubra os melhores negócios e serviços em [ipiau]. Guia da Cidade conecta você às empresas locais.">
<meta name="keywords" content="guia comercial, negócios, restaurantes, serviços, hotéis, [ipiau]">
<meta name="author" content="Guia da Cidade">
<title><?= htmlspecialchars($cat['name'] ?? 'Categoria') ?> - Guia da Cidade</title>
<link href="style.css" rel="stylesheet">
<link href="https://cdn.tailwindcss.com" rel="stylesheet">
 <script src="https://cdn.tailwindcss.com"></script>
 <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<h1 class="text-3xl font-bold mb-6">Negócios em <?= htmlspecialchars($cat['name'] ?? 'Categoria') ?></h1>

<div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
<?php while($b = $negocios->fetch_assoc()): ?>
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <img src="<?= $b['image'] ?>" class="w-full h-32 object-cover">
        <div class="p-4">
            <h4 class="text-lg font-semibold"><?= htmlspecialchars($b['name']) ?></h4>
            <p class="text-sm"><?= substr(strip_tags($b['description']),0,80) ?>...</p>
            <a href="detalhes.php?id=<?= $b['id'] ?>" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Ver Mais</a>
        </div>
    </div>
<?php endwhile; ?>
</div>
</body>
</html>
