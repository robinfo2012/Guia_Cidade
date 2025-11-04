<?php
require_once("config.php");

$q = trim($_GET['q'] ?? '');
$q_sql = $mysqli->real_escape_string($q);

// Busca nos negócios
$res = $mysqli->query("SELECT * FROM businesses 
                       WHERE name LIKE '%$q_sql%' 
                          OR description LIKE '%$q_sql%' 
                          OR address LIKE '%$q_sql%' 
                       ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Busca por <?= htmlspecialchars($q) ?> - Guia da Cidade</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

  <!-- topo -->
  <header class="bg-blue-600 text-white py-6 shadow-md mb-8">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Guia da Cidade</h1>
      <!--<a href="index.php" class="text-white hover:underline">Início</a>-->       
        <a href="index.php#negocios" class="inline-block px-5 py-2 bg-gray-800 text-white rounded hover:bg-red-700">← Voltar</a>

    </div>
  </header>

  <div class="max-w-7xl mx-auto px-6">
    <h2 class="text-2xl font-bold text-gray-700 mb-6">
      Resultados da busca por "<?= htmlspecialchars($q) ?>"
    </h2>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php if($res->num_rows > 0): ?>
        <?php while($b = $res->fetch_assoc()): ?>
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
        <p class="col-span-full text-center text-gray-500">Nenhum resultado encontrado.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- rodapé -->
  <footer class="bg-gray-800 text-gray-200 mt-12 py-6">
    <div class="max-w-7xl mx-auto px-6 text-center">
      &copy; <?= date("Y") ?> Guia da Cidade - Todos os direitos reservados.
    </div>
  </footer>

</body>
</html>
