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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($cat['name'] ?? 'Categoria') ?> - Guia da Cidade</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

  <!-- topo -->
  <!-- Header -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">🌆 Guia da Cidade</h1>
    <nav class="space-x-6">
      <!--<a href="index.php" class="hover:text-blue-600">Início</a>
      <a href="#categorias" class="hover:text-blue-600">Categorias</a>-->
       <div class="mt-6">
        <a href="index.php#negocios" class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-gray-900">← Voltar</a>
</div>
      
    </nav>
  </div>
</header>

  <!-- título -->
  <div class="max-w-7xl mx-auto px-6">
    <h2 class="text-3xl font-bold text-gray-700 mb-6">
      Negócios em <?= htmlspecialchars($cat['name'] ?? 'Categoria') ?>
    </h2>

    <!-- grid de negócios -->
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php if($negocios->num_rows > 0): ?>
        <?php while($b = $negocios->fetch_assoc()): ?>
          <div class="bg-white shadow-xl rounded-xl overflow-hidden border hover:shadow-2xl transition flex flex-col">
            <img src="<?= htmlspecialchars($b['image'] ?: 'sem-imagem.jpg') ?>" 
                 class="w-full h-40 object-cover" 
                 alt="<?= htmlspecialchars($b['name']) ?>">
            <div class="p-4 flex-1 flex flex-col">
              <h4 class="text-lg font-semibold text-blue-600 mb-2"><?= htmlspecialchars($b['name']) ?></h4>
              <p class="text-sm text-gray-600 flex-1"><?= substr(strip_tags($b['description']),0,80).'...' ?></p>
              <a href="detalhes.php?id=<?= $b['id'] ?>" 
                 class="mt-4 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                 Ver Mais
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="col-span-full text-center text-gray-500">Nenhum negócio encontrado nessa categoria.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- rodapé -->
 <!-- Footer -->
<footer id="contato" class="bg-gray-900 text-gray-300 py-10 mt-10">
  <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
    <div>
      <h4 class="text-lg font-bold text-white mb-3">Guia da Cidade</h4>
      <p>Conectando você aos melhores negócios locais.</p>
    </div>
    <div>
      <h4 class="text-lg font-bold text-white mb-3">Links Rápidos</h4>
      <ul class="space-y-2">
        <li><a href="#" class="hover:text-white">Início</a></li>
        <li><a href="#categorias" class="hover:text-white">Categorias</a></li>
        <li><a href="#negocios" class="hover:text-white">Negócios</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-lg font-bold text-white mb-3">Contato</h4>
      <p>Email: contato@seudominio.com</p>
      <p>WhatsApp: (73) 98228-9591</p>
    </div>
  </div>
  <div class="text-center text-sm text-gray-500 mt-8">© <?php echo date('Y');?> Guia da Cidade - Todos os direitos reservados.</div>
</footer>

</body>
</html>
