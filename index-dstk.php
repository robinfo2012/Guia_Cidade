<?php
include "config.php";
$today = date('Y-m-d');

// Empresas em destaque (pagas e ativas)
$res_destaque = $mysqli->query("
    SELECT * FROM businesses 
    WHERE plan IN ('basic','pro','premium') 
      AND paid_until >= '$today'
    ORDER BY FIELD(plan,'premium','pro','basic'), created_at DESC
    LIMIT 12
");

// Empresas free ou vencidas
$res_free = $mysqli->query("
    SELECT * FROM businesses 
    WHERE plan='free' OR paid_until < '$today'
    ORDER BY created_at DESC
    LIMIT 12
");

// Buscar categorias
$cats = $mysqli->query("SELECT id,name FROM categories ORDER BY name");
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Guia Comercial da Cidade</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">🌆 Guia da Cidade</h1>
    <nav class="space-x-6">
      <a href="#" class="hover:text-blue-600">Início</a>
      <a href="#categorias" class="hover:text-blue-600">Categorias</a>
      <a href="#destaques" class="hover:text-blue-600">Destaques</a>
      <a href="#negocios" class="hover:text-blue-600">Outros Negócios</a>
      <a href="#contato" class="hover:text-blue-600">Contato</a>
    </nav>
  </div>
</header>

<!-- Hero -->
<section class="bg-gradient-to-r from-blue-600 to-blue-400 text-white py-20 text-center">
  <h2 class="text-4xl md:text-5xl font-bold mb-6">Descubra os melhores negócios da sua cidade</h2>
  <p class="text-lg md:text-xl mb-8">Restaurantes, serviços, hospedagem e muito mais ao seu alcance</p>
  <form class="max-w-2xl mx-auto flex bg-white rounded-lg overflow-hidden shadow-lg">
    <input type="text" placeholder="O que você procura?" class="flex-1 p-4 outline-none text-gray-700">
    <button class="bg-blue-600 px-6 text-white font-semibold hover:bg-blue-700 transition">Buscar</button>
  </form>
</section>

<!-- Categorias -->
<section id="categorias" class="max-w-7xl mx-auto px-6 py-16">
  <h3 class="text-3xl font-bold mb-8 text-gray-700">Categorias em Destaque</h3>
  <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
    <?php while($c = $cats->fetch_assoc()): ?>
      <a href="?cat=<?= $c['id'] ?>" class="bg-white shadow rounded-xl p-6 text-center hover:bg-blue-50 hover:shadow-md transition">
        <span class="text-lg font-semibold text-blue-600"><?= htmlspecialchars($c['name']) ?></span>
      </a>
    <?php endwhile; ?>
  </div>
</section>

<!-- Empresas em Destaque -->
<section id="destaques" class="bg-yellow-50 py-16">
  <div class="max-w-7xl mx-auto px-6">
    <h3 class="text-3xl font-bold mb-8 text-gray-700">Empresas em Destaque</h3>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php while($b = $res_destaque->fetch_assoc()): 
        $border_color = $b['plan']=='premium' ? 'border-red-500' : ($b['plan']=='pro'?'border-blue-500':'border-yellow-400');
      ?>
      <div class="relative bg-white shadow-xl rounded-xl overflow-hidden border-2 <?= $border_color ?> hover:shadow-2xl transition flex flex-col">
        <div class="h-32 bg-gradient-to-r from-yellow-100 to-yellow-50"></div>
        <div class="p-4 flex-1 flex flex-col">
          <span class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs"><?= ucfirst($b['plan']) ?></span>
          <h4 class="text-lg font-semibold text-yellow-600 mb-2"><?= htmlspecialchars($b['name']) ?></h4>
          <p class="text-sm text-gray-600 flex-1"><?= substr(strip_tags($b['description']),0,80).'...' ?></p>
          <a href="detalhes.php?id=<?= $b['id'] ?>" class="mt-4 bg-yellow-500 text-white text-center py-2 rounded-lg hover:bg-yellow-600 transition">Ver Mais</a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Empresas Free -->
<section id="negocios" class="bg-gray-100 py-16">
  <div class="max-w-7xl mx-auto px-6">
    <h3 class="text-3xl font-bold mb-8 text-gray-700">Outros Negócios</h3>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php while($b = $res_free->fetch_assoc()): ?>
      <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
        <div class="h-32 bg-gradient-to-r from-gray-100 to-gray-50"></div>
        <div class="p-4 flex-1 flex flex-col">
          <h4 class="text-lg font-semibold text-gray-700 mb-2"><?= htmlspecialchars($b['name']) ?></h4>
          <p class="text-sm text-gray-600 flex-1"><?= substr(strip_tags($b['description']),0,80).'...' ?></p>
          <a href="detalhes.php?id=<?= $b['id'] ?>" class="mt-4 bg-gray-700 text-white text-center py-2 rounded-lg hover:bg-gray-800 transition">Ver Mais</a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

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
        <li><a href="#destaques" class="hover:text-white">Destaques</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-lg font-bold text-white mb-3">Contato</h4>
      <p>Email: contato@seudominio.com</p>
      <p>WhatsApp: (73) 98228-9591</p>
    </div>
  </div>
  <div class="text-center text-sm text-gray-500 mt-8">© <?= date('Y') ?> Guia da Cidade - Todos os direitos reservados.</div>
</footer>

</body>
</html>
