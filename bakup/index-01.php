<?php
$mysqli=new mysqli("localhost","root","","guia_cidade");
$mysqli->set_charset("utf8mb4");
$res=$mysqli->query("SELECT * FROM businesses ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Guia Comercial da Cidade</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<header class="bg-blue-600 text-white p-6 text-center text-2xl font-bold">Guia Comercial da Cidade</header>
<main class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
<?php while($b=$res->fetch_assoc()): ?>
<div class="bg-white shadow rounded overflow-hidden">
    <?php if(!empty($b['image'])): ?>
        <img src="<?php echo $b['image'];?>" class="h-40 w-full object-cover">
    <?php else: ?>
        <div class="h-40 w-full bg-gray-200 flex items-center justify-center text-gray-500">Sem imagem</div>
    <?php endif; ?>
    <div class="p-4">
        <h3 class="text-lg font-bold"><?php echo htmlspecialchars($b['name']);?></h3>
        <p class="text-gray-600 line-clamp-3"><?php echo nl2br(htmlspecialchars(substr($b['description'],0,100)));?>...</p>
        <a href="detalhes.php?slug=<?php echo $b['slug'];?>" class="mt-3 inline-block px-3 py-2 bg-blue-600 text-white rounded">Ver mais</a>
    </div>
</div>
<?php endwhile;?>
</main>
</body>
</html>
