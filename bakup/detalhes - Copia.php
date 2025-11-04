<?php
$mysqli = new mysqli("localhost","root","","guia_cidade");
if($mysqli->connect_errno){ die("Erro: ".$mysqli->connect_error); }
$mysqli->set_charset("utf8mb4");

$id = (int)($_GET['id'] ?? 0);
$res = $mysqli->query("SELECT b.*, c.name AS categoria 
                       FROM businesses b 
                       LEFT JOIN categories c ON b.category_id=c.id 
                       WHERE b.id=$id LIMIT 1");
$business = $res->fetch_assoc();
if(!$business){ die("Negócio não encontrado!"); }
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($business['name']); ?> - Guia Comercial</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family:Arial,sans-serif; background:#f3f4f6; margin:0; padding:0; }
.container { max-width:800px; margin:30px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
h1 { margin-top:0; }
.info { margin:10px 0; }
.btn-back { display:inline-block; margin-top:20px; padding:10px 16px; background:#3b82f6; color:#fff; border-radius:8px; text-decoration:none; }
.btn-back:hover { background:#2563eb; }
</style>
</head>
<body>
<div class="container">
    <?php if(!empty($business['image'])): ?>
    <div class="business-image">
        <img src="<?php echo htmlspecialchars($business['image']); ?>" alt="Imagem de <?php echo htmlspecialchars($business['name']); ?>">
    </div>
<?php endif; ?>
    <h1><?php echo htmlspecialchars($business['name']); ?></h1>
    <p><strong>Categoria:</strong> <?php echo htmlspecialchars($business['categoria']); ?></p>
    <p><strong>Descrição:</strong><br><?php echo nl2br(htmlspecialchars($business['description'])); ?></p>
    <div class="info"><strong>Endereço:</strong> <?php echo htmlspecialchars($business['address']); ?></div>
    <div class="info"><strong>Telefone:</strong> <?php echo htmlspecialchars($business['phone']); ?></div>
    <div class="info"><strong>WhatsApp:</strong> <?php echo htmlspecialchars($business['whatsapp']); ?></div>
    <div class="info"><strong>Site:</strong> <a href="<?php echo $business['website'];?>" target="_blank"><?php echo $business['website'];?></a></div>
    <div class="info"><strong>Facebook:</strong> <a href="<?php echo $business['facebook'];?>" target="_blank"><?php echo $business['facebook'];?></a></div>
    <div class="info"><strong>Instagram:</strong> <a href="<?php echo $business['instagram'];?>" target="_blank"><?php echo $business['instagram'];?></a></div>
    <a href="index.php" class="btn-back">⬅ Voltar</a>
</div>
</body>
</html>
