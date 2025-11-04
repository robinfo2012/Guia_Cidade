<!-- Topo do Painel Admin -->
<div class="bg-white shadow p-4 flex justify-between items-center">
  <h1 class="text-xl font-bold text-blue-600">
      <?php
        $page_title = $page_title ?? 'Painel Admin';
        echo $page_title;
      ?>
  </h1>
  <meta charset="utf-8">
  <title>Painel Administrativo </title>
  
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">

  <div class="space-x-3">
    <a href="dashboard.php" class="px-4 py-2 <?= ($page_title==='Dashboard')?'bg-red-600 text-white rounded':'bg-gray-200 rounded hover:bg-gray-300'; ?>">🏠 Dashboard</a>
    <a href="index.php" class="px-4 py-2 <?= ($page_title==='Negócios')?'bg-blue-600 text-white rounded':'bg-orange-200 rounded hover:bg-gray-300'; ?>">Negócios</a>
    <a href="usuarios.php" class="px-4 py-2 <?= ($page_title==='Usuários')?'bg-blue-600 text-white rounded':'bg-green-200 rounded hover:bg-gray-300'; ?>">👤 Usuários</a>
    <a href="categorias.php" class="px-4 py-2 <?= ($page_title==='Categorias')?'bg-blue-600 text-white rounded':'bg-blue-200 rounded hover:bg-gray-300'; ?>">📂 Categorias</a>
    <a href="settings.php" class="px-4 py-2 <?= ($page_title==='Configurações')?'bg-blue-600 text-white rounded':'bg-pink-200 rounded hover:bg-gray-300'; ?>">⚙ Configurações</a>
    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded">🚪 Sair</a>
  </div>
</div>
