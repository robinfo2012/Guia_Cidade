<?php
include 'config.php';

// Pegar categorias
$cats = $mysqli->query("SELECT id,name FROM categories ORDER BY name");

// Pegar negócios, opcionalmente filtrando por categoria
$cat_id = intval($_GET['cat'] ?? 0);
if($cat_id>0){
    $stmt = $mysqli->prepare("SELECT * FROM businesses WHERE category_id=? ORDER BY name");
    $stmt->bind_param('i',$cat_id);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $mysqli->query("SELECT * FROM businesses ORDER BY name");
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Guia Comercial</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">
<div class="public-container">

<h1>Guia Comercial da Cidade</h1>

<!-- Filtro de categorias -->
<div class="filter-bar">
<button class="<?php if($cat_id===0) echo 'active'; ?>" onclick="window.location='index.php'">Todas</button>
<?php while($c=$cats->fetch_assoc()): ?>
<button class="<?php if($cat_id==$c['id']) echo 'active'; ?>" onclick="window.location='?cat=<?php echo $c['id']; ?>'"><?php echo htmlspecialchars($c['name']); ?></button>
<?php endwhile; ?>
</div>

<!-- Grid de negócios -->
<div class="card-grid">
<?php while($b=$res->fetch_assoc()): ?>
<div class="card">
<h3><?php echo htmlspecialchars($b['name']); ?></h3>
<p><?php echo htmlspecialchars($b['description']); ?></p>
<p><strong>Endereço:</strong> <?php echo htmlspecialchars($b['address']); ?></p>
<p><strong>Telefone:</strong> <?php echo htmlspecialchars($b['phone']); ?></p>
<?php if($b['whatsapp']): ?><p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($b['whatsapp']); ?></p><?php endif; ?>
<div>
<?php if($b['website']): ?><a href="<?php echo $b['website']; ?>" target="_blank">Site</a> <?php endif; ?>
<?php if($b['facebook']): ?><a href="<?php echo $b['facebook']; ?>" target="_blank">Facebook</a> <?php endif; ?>
<?php if($b['instagram']): ?><a href="<?php echo $b['instagram']; ?>" target="_blank">Instagram</a> <?php endif; ?>
</div>
</div>
<?php endwhile; ?>
</div>

</div>
</body>
</html>
