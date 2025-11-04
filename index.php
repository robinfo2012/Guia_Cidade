<?php include"config.php"; ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Guia Comercial da Cidade</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="description" content="Descubra os melhores negócios e serviços em [ipiau]. Guia da Cidade conecta você às empresas locais.">
<meta name="keywords" content="guia comercial, negócios, restaurantes, serviços, hotéis, [ipiau]">
<meta name="author" content="Guia da Cidade">

</head>

<body class="bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">🌆 Guia da Cidade</h1>
    <nav class="space-x-6">
      <a href="index.php" class="hover:text-blue-600">Início</a>
      <a href="#categorias" class="hover:text-blue-600">Categorias</a>
      <a href="#negocios" class="hover:text-blue-600">Negócios</a>
      <a href="#contato" class="hover:text-blue-600">Contato</a>
    </nav>
  </div>
</header><?php 
$today = date('Y-m-d');

$termo = trim($_GET['busca'] ?? '');
$where_busca = "";
if(!empty($termo)){
    $termo_esc = $mysqli->real_escape_string($termo);
    $where_busca = " AND (name LIKE '%$termo_esc%' OR description LIKE '%$termo_esc%')";
}


// Empresas em destaque
$res_destaque = $mysqli->query("
    SELECT * FROM businesses 
    WHERE plan IN ('basic','pro','premium') 
      AND paid_until >= '$today'
      $where_busca
    ORDER BY FIELD(plan,'premium','pro','basic'), created_at DESC
    LIMIT 12
");

// Empresas free
$res_free = $mysqli->query("
    SELECT * FROM businesses 
    WHERE (plan='free' OR paid_until < '$today')
      $where_busca
    ORDER BY created_at DESC
    LIMIT 12
");
// Buscar empresas em destaque (pagas e ativas)
$res_destaque = $mysqli->query("
    SELECT * FROM businesses 
    WHERE plan IN ('basic','pro','premium') 
      AND paid_until >= '$today'
    ORDER BY FIELD(plan,'premium','pro','basic'), created_at DESC
    LIMIT 12
");

// Empresas free
$res_free = $mysqli->query("
    SELECT * FROM businesses 
    WHERE plan='free' OR paid_until < '$today'
    ORDER BY created_at DESC
    LIMIT 12
");
 ?>
 <!-- Slider Show -->
<?php include "slideshow.php"; ?>

<!-- Hero -->
<section class="bg-gradient-to-r from-blue-600 to-blue-400 text-white py-20 text-center">
  <h2 class="text-4xl md:text-5xl font-bold mb-6">Descubra os melhores negócios da sua cidade</h2>
  <p class="text-lg md:text-xl mb-8">Restaurantes, serviços, hospedagem e muito mais ao seu alcance</p>

 <form class="max-w-2xl mx-auto flex bg-white rounded-lg overflow-hidden shadow-lg" method="get" action="buscar.php">
  <input type="text" 
             name="q" 
             placeholder="Buscar negócios..."
             class="w-full px-4 py-2 text-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none">
  <button type="submit" class="bg-green-600 px-6 text-lg font-semibold hover:bg-blue-700 transition">Buscar</button>
</form>
</section>

<!-- destak -->
<section id="destaques" class="bg-yellow-50 py-16">
  <div class="max-w-7xl mx-auto px-6">
    <h3 class="text-3xl font-bold mb-8 text-gray-700">Empresas em Destaque</h3>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php while($b = $res_destaque->fetch_assoc()): ?>
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border-2 border-yellow-400 hover:shadow-2xl transition flex flex-col">
          
          <?php if (!empty($b['image'])): ?>
            <img src="<?= htmlspecialchars($b['image']) ?>" 
                 alt="<?= htmlspecialchars($b['name']) ?>" 
                 class="h-32 w-full object-cover">
          <?php else: ?>
            <img src="assets/no-image.png" 
                 alt="Sem imagem" 
                 class="h-32 w-full object-cover">
          <?php endif; ?>
          
          <div class="p-4 flex-1 flex flex-col">
            <h4 class="text-lg font-semibold text-yellow-600 mb-2"><?= htmlspecialchars($b['name']) ?></h4>
            <p class="text-sm text-gray-600 flex-1"><?= substr(strip_tags($b['description']),0,80).'...' ?></p>
            <a href="detalhes.php?id=<?= $b['id'] ?>" class="mt-4 bg-yellow-500 text-white text-center py-2 rounded-lg hover:bg-yellow-600 transition">Ver Mais</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>
<!-- destak -->


<!-- Categorias -->
<section id="categorias" class="max-w-7xl mx-auto px-6 py-16">
  <h3 class="text-3xl font-bold mb-8 text-gray-700">Categorias em Destaque</h3>
  <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
    <?php
    $cats=$mysqli->query("SELECT id,name FROM categories ORDER BY name");
        while($c=$cats->fetch_assoc()):
      $result = $mysqli->query("SELECT id, name, description, category_id, image FROM businesses ORDER BY id DESC");


    ?>
     <a href="categoria.php?id=<?= $c['id'];?>" class="bg-white shadow rounded-xl p-6 text-center hover:bg-blue-50 hover:shadow-md transition">
    <span class="text-lg font-semibold text-blue-600"><?= htmlspecialchars($c['name']); ?></span>
</a>

    <?php endwhile; ?>
  </div>
</section>

<!-- Negócios -->
<section id="negocios" class="bg-gray-100 py-16">
  <div class="max-w-7xl mx-auto px-6">
    <h3 class="text-3xl font-bold mb-8 text-gray-700">Negócios em Destaque</h3>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php
      $res=$mysqli->query("SELECT * FROM businesses ORDER BY created_at DESC LIMIT 12");
      while($b=$res->fetch_assoc()):
      ?>
      <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
        
        <!-- Imagem do negócio -->
       <?php if(!empty($b['image'])): ?>
  <img src="<?php echo htmlspecialchars($b['image']); ?>" class="h-32 w-full object-cover">
<?php else: ?>
  <div class="h-32 w-full bg-gray-200 flex items-center justify-center text-gray-400">Sem imagem</div>
<?php endif; ?>


        <div class="p-4 flex-1 flex flex-col">
          <h4 class="text-lg font-semibold text-blue-600 mb-2"><?php echo htmlspecialchars($b['name']);?></h4>
          <p class="text-sm text-gray-600 flex-1"><?php echo substr(strip_tags($b['description']),0,80).'...';?></p>
          <a href="detalhes.php?id=<?php echo $b['id'];?>" class="mt-4 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">Ver Mais</a>
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
